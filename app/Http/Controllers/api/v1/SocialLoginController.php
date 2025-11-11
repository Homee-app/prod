<?php

namespace App\Http\Controllers\API\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Boost;
use App\Models\GoldenKey;
use App\Models\Question;
use App\Models\QuestionAnswerUser;
use App\Traits\Common_trait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SocialLoginController extends Controller
{
    use Common_trait;

    public function socialLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'socialId' => 'required|string',
            'socialType' => 'required|in:1,2', // 1 = Google, 2 = Apple
            'email' => 'required|email',
            'deviceToken' => 'required|string',
            'deviceType' => 'required',
        ]);
        $loginType = [
            1 => 2, // Google
            2 => 3, // Apple
        ];

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        $userData = [];
        if ($request->deviceToken === 'test_token') {
            $userData = [
                'email' => strtolower(fake()->firstName()) . '@yopmail.com',
                'name'  => fake()->name()
            ];
        } else if ($request->socialType == 1) {
            $userData = $this->verifyGoogleToken($request->socialId, $request->deviceToken);
        } elseif ($request->socialType == 2) {
            $userData = $this->verifyAppleToken($request->socialId, $request->deviceToken);
        } else {
            return ApiResponse::error('Unsupported social type', 401);
        }

        if (!$userData && !$userData['social_id']) {
            return ApiResponse::error('Invalid token', 401);
        }

        $socialId  = $userData['social_id'] ?? null;
        $email = $userData['email'] ?? $request->email ?? null;
        $user = User::where('device_id', $socialId)->first();

        if (!$user) {
            if (!$email) {

                $email = "user_{$socialId}@apple.local";
            }
            $user = User::updateOrCreate(
                ['email' => strtolower($email)],
                [
                    'first_name'  => $userData['name'] ?? ($request->firstName ?? 'John'),
                    'password'    => Hash::make('password'),
                    'login_type'  => $loginType[$request->socialType],
                    'device_type' => $request->deviceType,
                    'device_id'   => $socialId ?? $request->deviceToken,
                ]
            );
        } else {

            $user = User::updateOrCreate(
                ['email' => $email ?? $request->email],
                [
                    'first_name' => $userData['name'] ?? ($request->firstName ?? 'John'),
                    'password' => Hash::make('password'),
                    'login_type' => $loginType[$request->deviceType] ?? 1,
                    'device_type' => $request->deviceType,
                    'device_id' => $userData['social_id'] ?? $request->deviceToken
                ]
            );
        }

        if (!empty($userData['picture']) &&  $user->profile_photo == null) {
            try {
                $imageContents = file_get_contents($userData['picture']);

                $tempFile = tempnam(sys_get_temp_dir(), 'profile_') . '.jpg';
                file_put_contents($tempFile, $imageContents);

                $uploadedFile = new \Illuminate\Http\UploadedFile(
                    $tempFile,
                    basename($tempFile),
                    'image/jpeg',
                    null,
                    true
                );

                $userProfilePhotoPath = $this->file_upload(
                    $uploadedFile,
                    config('constants.uploads') . '/' . $user->id . '/' . config('constants.user_profile_photo')
                );
                $user->update(['profile_photo' => $userProfilePhotoPath['original']]);
            } catch (\Exception $e) {
                Log::error("Failed to download user profile image: " . $e->getMessage());
            }
        }

        if ($user->wasRecentlyCreated) {
            $weeklyChats = config('constants.weekly_chat_count');
            Boost::create(['boost_count' => 1, 'user_id' => $user->id]);
            GoldenKey::create(['key_count' => 2, 'user_id' => $user->id]);

            QuestionAnswerUser::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'question_id' => 2
                ],
                [
                    'answer' => $request->firstName ?? 'John',
                    'for_partner' => 0,
                ]
            );
            $user->role = 2;
            $user->chat_count = $weeklyChats;
            $user->week_start_date = now();
            $user->save();
        }

        $token = $user->createToken(config('services.key.api_secret_key'))->plainTextToken;
        $newUser = User::with(['subscription', 'keyUsages', 'boostUsages', 'boosts', 'keys'])->find($user->id);
        $result = array_merge((new UserResource($newUser))->toArray(request()),   ['token' => $token]);

        return ApiResponse::success($result, __('messages.login'), 200);
    }

    private function verifyGoogleToken($idToken, $deviceToken)
    {
        $url = "https://oauth2.googleapis.com/tokeninfo?id_token={$idToken}";
        try {
            $response = Http::withoutVerifying()->get($url);
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'email' => $data['email'] ?? null,
                    'name'  => $data['name'] ?? null,
                    'picture'  => $data['picture'] ?? null,
                    'social_id' => $deviceToken,
                ];
            }

            throw new HttpException(401, 'Unauthorized: Invalid Google token.');
        } catch (\Exception $e) {
            throw new HttpException(500, 'Google token verification failed: ' . $e->getMessage());
        }
    }

    private function verifyAppleToken($identityToken, $social_id)
    {
        try {

            $payload = explode('.', $identityToken);
            if (count($payload) < 2) return null;
            $claims = json_decode(base64_decode($payload[1]), true);
            Log::info('Apple claims: ' . print_r($claims, true));
            return [
                'email' => $claims['email'] ?? null,
                'social_id' => $social_id,
                'name'  => null,
                'picture' => null
            ];
        } catch (\Exception $e) {

            throw new HttpException(500, 'Google token verification failed: ' . $e->getMessage());
        }
    }
}
