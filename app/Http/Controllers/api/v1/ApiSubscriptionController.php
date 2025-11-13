<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\ApiResponse;
use App\Models\Purchase;
use App\Models\Room;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Traits\PurchaseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiSubscriptionController extends BaseApiController
{
    use PurchaseTrait;

    public function buyProducts(Request $request)
    {
        $request->validate([
            'purchase_token' => 'required|string',
            'platform' => 'required|in:ios,android',
            'product_id' => 'required|string',
            'transaction_date' => 'required',
            'amount' => 'nullable'
        ]);
        $platformArray = [
            'android' => 2,
            'ios' => 1,
        ];
        $roleArray = [
            2 => 'tenant',
            3 => 'owner'
        ];
        $user = $request->user();
        $plan = SubscriptionPlan::where('product_id', $request->product_id)
            ->where('platform', $platformArray[$request->platform])
            ->where('type', $roleArray[$user->role])
            ->where('status', '1')
            ->first();
        if (!$plan) {
            return ApiResponse::error('Invalid product selected', 404);
        }
        try {
            DB::beginTransaction();
            if ($request->platform === 'android') {
                $transactionId = $request->purchase_token;
                $verified = $this->verifyAndroidPurchase($request->purchase_token, $request->product_id);
            } else {
                $verified = $this->verifyIosPurchase($request->purchase_token);
                $decodedToken = $this->decodeIosTransaction($request->purchase_token) ?? [];
                if (!empty($decodedToken)) {
                    $transactionId = $decodedToken['transactionId'];
                }
            }
            $msg = '';
            if ($verified['status'] === true) {
                $this->addPurchaseHistory($request, $user, $plan, $request->amount, $transactionId);
                switch ($plan->value_type) {
                    case 'boost':
                        $user->boosts()->increment('boost_count', $plan->value);
                        $msg = __('messages.boost_added');
                        break;
                    case 'key':
                        $user->keys()->increment('key_count', $plan->value);
                        $msg =  __('messages.key_added');
                        break;
                    default:
                        $this->addSubscription($user, $plan);
                        $msg = __('messages.item_activated', ['item' => 'Subscription']);
                        $boost_count = 2;
                        $key_count = 3;
                        if ($user->role == 3) {
                            $boost_count = 3;
                            $key_count = 2;
                        }
                        $user->boosts()->increment('boost_count', $boost_count);
                        $user->keys()->increment('key_count', $key_count);
                        break;
                }
                DB::commit();
                return ApiResponse::success([
                    'message' => $msg,
                ]);
            } else {
                DB::rollBack();
                return ApiResponse::error(__('messages.verification_failed', ['item' => 'token']));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $error = $e->getMessage() . ' - ' . $e->getLine();
            Log::error($error);
            return ApiResponse::error(__('messages.something_went_wrong'));
        }
    }

    public function history(Request $request)
    {
        $user = $request->user();
        $history = $user->purchases()->orderBy('created_at', 'desc')->get();

        return ApiResponse::success($history);
    }

    public function resourcePage($type = null)
    {
        $basePath = 'assets/resources/';
        $resources = [
            'pre-agreement' => [
                'name' => 'Pre-Agreement Checklist',
                'file' => 'pre-agreement-checklist.pdf',
                'img'  => 'assets/resources/Images/pre-agreement.jpg',
            ],
            'housemate-agreement' => [
                'name' => 'Housemate Agreement',
                'file' => 'housemate-agreement-checklist.pdf',
                'img'  => 'assets/resources/Images/housemate-agreement.jpg',
            ],
        ];

        if ($type && isset($resources[$type])) {
            $url = asset($basePath . $resources[$type]['file']);
            return ApiResponse::success(['url' => $url]);
        }

        $response = collect($resources)->map(function ($res) use ($basePath) {
            return [
                'name' => $res['name'],
                'img'  => asset($res['img']),
                'url'  => asset($basePath . $res['file']),
            ];
        })->values();

        return ApiResponse::success($response);
    }

    public function useBoost(Request $request)
    {
        $curreantUser = $request->user();
        $boost = $curreantUser->boosts()->first();
        $currentTime = now();
        $expireTime = is_production() ? $currentTime->addDay() : $currentTime->addHour();
        if (!$boost || $boost->boost_count <= 0) {
            return ApiResponse::error(__('messages.no_boost'));
        }
        $boost->decrement('boost_count', 1);
        $usage = $curreantUser->boostUsages()->create([
            'used_at'   => $currentTime,
            'expires_at' => $expireTime,
        ]);
        return ApiResponse::success([
            'message' => __('messages.profile_on_top'),
            'data'   => $usage
        ]);
    }

    public function useRoomBoost(Request $request)
    {
        $rules = [
            'room_id' => 'required|exists:' . Room::class . ',id',
        ];
        $request->validate($rules);
        $currentTime = now();
        $expireTime = is_production() ? $currentTime->addDay() : $currentTime->addHour();
        $curreantUser = $request->user();
        $roomId = $request->room_id;
        $room = Room::find($roomId);
        $boost = $room->boosts()->first();
        $userBoost = $curreantUser->boosts()->first();
        if (!$boost || $boost->boost_count <= 0) {
            return ApiResponse::error(__('messages.no_boost'));
        }
        $userBoost->decrement('boost_count', 1);
        $data = $room->boostUsages()->create([
            'used_at'   => $currentTime,
            'expires_at' => $expireTime,
        ]);
        return ApiResponse::success([
            'message' => __('messages.room_on_top'),
            'data'   => $data
        ]);
    }

    public function useKey(Request $request)
    {
        $curreantUser = $request->user();
        $currentTime = now();
        $key = $curreantUser->keys()->first();
        if (!$key || $key->key_count <= 0) {
            return ApiResponse::error(__('messages.no_key'));
        }
        $key->decrement('key_count', 1);
        $data = $curreantUser->keyUsages()->create([
            'used_at'   => $currentTime,
        ]);
        return ApiResponse::success([
            'message' => __('messages.item_activated',['item' => 'Key']),
            'data'   => $data
        ]);
    }

    public function getKeyCount(Request $request)
    {
        $curreantUser = $request->user();
        return ApiResponse::success(['count' => $curreantUser->key_count]);
    }

    public function addKey(Request $request)
    {
        $rules = [
            'user_id' => 'required|exists:' . User::class . ',id',
        ];
        $request->validate($rules);
        $curreantUser = User::find($request->user_id);
        $key = $curreantUser->keys()->first();
        if (!$key) {
            return ApiResponse::error(__('messages.no_key'));
        }
        $key->increment('key_count', 1);
        return ApiResponse::success([
            'message' =>  __('messages.item_activated',['item' => 'Key']),
        ]);
    }

    protected function addSubscription($user, $plan)
    {
        $started_at = $startedAt = now();
        $expires_at = is_production() ? $started_at->{$plan->interval_method}($plan->value) : $started_at->addHour();
        Subscription::create(
            [
                'user_id' => $user->id,
                'product_id' => $plan->product_id,
                'plan_id' => $plan->id,
                'status' => '1',
                'amount' => $plan->price,
                'user_role' => $user->role,
                'started_at' => $startedAt,
                'expires_at' => $expires_at
            ]
        );
        $user->week_start_date = $expires_at->startOfHour();
        $user->chat_count = 0;
        $user->is_subscribed = 1;
        $user->save();
    }

    protected function addPurchaseHistory($request, $user, $plan, $amount = 0,  $transactionId)
    {
        $transactionDate = make_transaction_date($request->transaction_date);
        $purchasedData = [
            'user_id' => $user->id,
            'type' => $plan->value_type,
            'product_id' => $request->product_id,
            'platform' => $request->platform,
            'amount' => $amount == 0 ? $plan->price : $amount,
            'purchase_token' => $request->purchase_token,
            'transaction_id' => $transactionId ?? null,
            'status' => 2,
            'transaction_date' => $transactionDate,
        ];

        Log::info('This a user transaction data : ', $purchasedData);
        return  Purchase::create($purchasedData);
    }

    public function currentPlan(Request $request)
    {
        $currentUser = $request->user();
        $subscription = $currentUser->active_subscription;
        return ApiResponse::success([
            'status' => $subscription?->plan ? true : false,
            'upto' => $subscription?->plan?->value,
            'type' => $subscription?->plan?->value_type,
            'expires_at' => $subscription?->expires_at,
            'price' => $subscription?->plan?->price ?? 0,
            'user_type' => $subscription?->role?->name ?? ''
        ]);
    }
}
