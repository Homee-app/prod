<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use App\Traits\Common_trait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    use Common_trait;
    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::query()->with(['userIdentity'])
            ->where('role', '!=', 1) // Exclude admin users
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                });
            })
            ->paginate(10);

        $users->appends(['search' => $search]);

        return view('admin.manage-users.index', compact('users', 'search'));
    }

    public function userDetails(Request $request, $id)
    {
        $data['user'] = User::with('userIdentity')->findOrFail($id);

        $data['all_questions_data'] = Question::with([
            'options:id,question_id,label_for_web,value,min_val,max_val',
            'userAnswer' => function ($query) use ($id) {
                $query->where('user_id', $id)
                    ->where('for_partner', false)
                    ->select('question_id', 'option_id', 'answer', 'id', 'for_partner');
            },
            'partnerAnswer' => function ($query) use ($id) {
                $query->where('user_id', $id)
                    ->where('for_partner', true)
                    ->select('question_id', 'option_id', 'answer', 'id', 'for_partner');
            },
            'privacySetting' => function ($query) use ($id) {
                $query->where('user_id', $id)->select('question_id', 'is_hidden');
            }
        ])
            ->whereHas('userAnswer', function ($query) use ($id) {
                $query->where('user_id', $id)
                    ->where('for_partner', false);
            })
            ->whereIn('showing_in', [1, 3])
            ->select('id', 'title_for_web', 'type_for_web', 'question_order_for_web', 'section', 'question_for', 'showing_in')
            ->orderBy('question_for')
            ->get()
            ->groupBy('question_for')
            ->toArray();

        return view('admin.manage-users.user-detail', $data);
    }

    public function userSave(Request $req)
    {
        $req->validate([
            'first_name' => 'required|string|min:3|max:255|regex:/^[a-zA-Z]+$/',
            'last_name' => 'required|string|min:3|max:255|regex:/^[a-zA-Z]+$/',
            'email'      => 'required|email|unique:' . config('tables.users'),
            'password'   => 'required|min:6|max:15',
            'profile_photo'     => 'required|file|mimes:jpg,jpeg,png',
            'status'     => 'required|in:0,1',
        ]);

        $img = $this->file_upload($req->file('profile_photo'), config('constants.uploads') . '/' . config('constants.user_profile_photo'));

        $user = new User();
        $user->first_name = $req->first_name;
        $user->last_name  = $req->last_name;
        $user->email      = $req->email;
        $user->profile_photo      = $img['original'];
        $user->role      = 3;
        $user->status      = $req->status;
        $user->password   = Hash::make($req->password);

        if ($user->save()) {
            return redirect()->back()->with('flash-success', __('messages.create_success', ['item' => 'User']));
        } else {
            return redirect()->back()->with('Oops! ' . __('messages.something_went_wrong'));
        }
    }

    public function userEdit($id)
    {
        $singleUser = User::findOrFail($id);
        return view('admin.manage-users.edit', compact('singleUser'));
    }

    public function userUpdate(Request $req, $userId)
    {
        $req->validate([
            'first_name' => 'required|string|min:3|max:255|regex:/^[a-zA-Z]+$/',
            'last_name' => 'required|string|min:3|max:255|regex:/^[a-zA-Z]+$/',
            'email' => 'required|email|unique:' . config('tables.users') . ',email,' . $userId,
            'password'   => 'nullable|min:6|max:15',
            'profile_photo'     => 'file|mimes:jpg,jpeg,png',
            'status'     => 'required|in:0,1',
        ]);

        $user = User::findOrFail($userId);

        $img = $user->profile_photo;

        if ($req->file('profile_photo') != '') {
            $img = $this->file_upload($req->file('profile_photo'), config('constants.uploads') . '/' . config('constants.user_profile_photo'));

            if ($img['original'] && $user->profile_photo && file_exists(public_path($user->profile_photo))) {
                unlink(public_path($user->profile_photo));
            }
        }

        $user->first_name = $req->first_name;
        $user->last_name  = $req->last_name;
        $user->email      = $req->email;
        $user->profile_photo      = $img['original'];
        $user->status      = $req->status;
        $user->password   = $req->password ? Hash::make($req->password) : $user->password;

        if ($user->save()) {
            return redirect()->back()->with('flash-success', __('messages.update_success', ['item' => 'User']));
        } else {
            return redirect()->back()->with('Oops! ' . __('messages.something_went_wrong'));
        }
    }

    public function verifyIdentity(Request $request, User $user)
    {
        $status = $request->input('status');

        if (!in_array($status, ['approved', 'rejected'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status provided.',
            ], 422);
        }

        if (!$user->userIdentity) {
            return response()->json([
                'success' => false,
                'message' => 'User does not have an identity record.',
            ], 404);
        }

        try {
            DB::beginTransaction();

            $user->userIdentity->verification_status = $status;
            $user->userIdentity->verified_at = $status === 'approved' ? now() : null;
            $user->userIdentity->save();
            $user->userIdentity->refresh();
            DB::commit();

            app(NotificationService::class)
                ->send(
                    type: 'user_identity',
                    id: $user->id,
                    name: $user->first_name,
                    message: $status === 'approved' ? "You’re all set 🙌 Your ID’s been verified" : "⚠️ Your ID couldn’t be verified. Please try again.",
                    image: null,
                    meta: ['verification_status' => $status],
                    senderId: null
                );

            $user->boosts()->increment('boost_count', 1);

            return response()->json([
                'success' => true,
                'message' => "User identity {$status} successfully.",
                'data' => [
                    'id_type' => $user->userIdentity->id_type,
                    'verified_at' => $user->userIdentity->verified_at
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user identity status.',
                'error' => $e->getMessage(), // For debugging
            ], 500);
        }

    }
}
