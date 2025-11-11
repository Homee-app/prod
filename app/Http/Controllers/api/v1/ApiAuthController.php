<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\ApiResponse;
use App\Http\Resources\Custom\BlockUserResource;
use App\Http\Resources\UserResource;
use App\Models\GoldenKey;
use App\Models\User;
use App\Models\Room;
use App\Models\TenantProfile;
use App\Models\UserIdentity;
use App\Traits\Common_trait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use App\Models\Verification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use App\Mail\ForgotPassword;
use App\Models\Boost;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


class ApiAuthController extends BaseApiController
{
    use Common_trait;

    const ROLE_ADMIN = 1;
    const ROLE_TENANT = 2;
    const ROLE_OWNER = 3;

    public function signup(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'email'       => 'required|email',
            'password'    => [
                'required',
                'string',
                'min:8',
                'max:15',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
            ],
            'device_type' => 'required|in:1,2',
            'device_id'   => 'required',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ], [
            'password.required' => 'The password field is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.max' => 'Password must not exceed 15 characters.',
            'password.regex' => 'Password must include at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data'    => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $email = strtolower($req->email);

        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            if (in_array($existingUser->role, ['1'])) {
                return $this->sendError(__('messages.admin_email_exists_login'), [], 400);
            } else if (in_array($existingUser->role, ['2', '3'])) {
                return $this->sendError(__('messages.email_exists_login'), [], 400);
            }
        }

        $verification = Verification::where('value', $email)
            ->where('type', 1) // email
            ->where('status', 1) // verified
            ->first();

        if (!$verification) {
            return $this->sendError(__('messages.email_not_verify_otp'), [], 403);
        }

        try {
            $weeklyChats = config('constants.weekly_chat_count');
            DB::beginTransaction();
            $user = new User();
            $user->email = $email;
            $user->password = Hash::make($req->password);
            $user->device_type = $req->device_type;
            $user->device_id = $req->device_id;
            $user->chat_count = $weeklyChats;
            $user->week_start_date = now();

            if ($req->longitude) {
                $user->longitude = $req->longitude;
            }
            if ($req->latitude) {
                $user->latitude = $req->latitude;
            }
            $user->role = 2;
            if ($user->save()) {
                $tenantProfile = new TenantProfile();
                $tenantProfile->user_id = $user->id;
                // is_teamup defaults to false, so no need to set it explicitly unless you want to override
                $tenantProfile->save();
                $verification->delete(); // only delete now
                $token = $user->createToken(config('services.key.api_secret_key'))->plainTextToken;
                $result = array_merge(
                    (new UserResource($user))->toArray(request()),
                    ['token' => $token]
                );

                Boost::create(['boost_count' => 1, 'user_id' => $user->id]);
                GoldenKey::create(['key_count' => 2, 'user_id' => $user->id]);

                DB::commit();
                return $this->sendResponse($result, __('messages.register', ['item' => 'User']));
            } else {
                DB::rollBack();
                return $this->sendError(__('messages.register_failed'), [], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(__('messages.exception_occurred_during_registration'), [$e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_type' => 'required|in:1,2',
            'device_id' => 'required|string',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->whereIn('role', [2, 3])->first(); // not a admin user

        if (!$user) {
            return $this->sendError(__('messages.email_not_registed'), null, 403);
        }

        if ($user->status == 0) {
            return $this->sendError(__('messages.account_is_deactivate'), null, 403);
        }

        // Check password
        if (!Hash::check($request->password, $user->password)) {
            return $this->sendError(__('messages.invalid_credentials'), null, 401);
        }

        User::whereDeviceId($request->device_id)
            ->where('id', '!=', $user->id)
            ->update([
                'device_id' => null,
                'device_type' => null
            ]);

        $user->device_type = $request->device_type;
        $user->device_id = $request->device_id;

        if ($user->save()) {
            $token = $user->currentAccessToken();
            if ($token) {
                $token->delete();
            }
            $user->tokens()->delete();
            $newToken = $user->createToken(config('services.key.api_secret_key'))->plainTextToken;
            $newUser = User::with(['subscription', 'keyUsages', 'boostUsages', 'boosts', 'keys'])->find($user->id);
            $result = array_merge((new UserResource($newUser))->toArray(request()),   ['token' => $newToken]);
            return $this->sendResponse($result, __('messages.you_have_sign_in_successfully'));
        } else {
            return $this->sendError(__('messages.something_went_wrong'), [], 404);
        }
    }

    public function sendOtpVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'value' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        return;
                    }
                    if (!ctype_digit($value)) {
                        $fail('The value must be a valid email.');
                    }
                },
            ],
            'type' => 'required|in:email',
            'device_type' => 'required|in:1,2',
            'device_id' => 'required',
        ]);

        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();
            return $this->sendError($firstError, [], 422);
        }

        if (!filter_var($request->value, FILTER_VALIDATE_EMAIL)) {
            return $this->sendError(__('messages.invalid_email'), [], 400);
        }

        $email = strtolower($request->value);

        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            return $this->sendError(__('messages.email_exists_login'), [], 400);
        }

        $verification = Verification::where('value', strtolower($request->value))
            ->where('type', 1)
            ->where('device_id', $request->device_id)
            ->first();

        if ($verification && $verification->status == 1) {
            return $this->sendError(__('messages.password_is_missing'), [], 400);
        }

        $otp = rand(100000, 999999);
        $expiryTime = Carbon::now()->addMinutes(10);

        if ($verification) {
            if ($verification->device_id == $request->device_id) {
                $verification->otp = $otp;
                $verification->expires_at = $expiryTime;
                $verification->otp_type = 1;
                $verification->save();
            } else {
                $verification = new Verification();
                $verification->value = strtolower($request->value);
                $verification->type = 1;
                $verification->device_type = $request->device_type;
                $verification->device_id = $request->device_id;
                $verification->otp = $otp;
                $verification->expires_at = $expiryTime;
                $verification->otp_type = 1;
                $verification->save();
            }
        } else {
            $verification = new Verification();
            $verification->value = strtolower($request->value);
            $verification->type = 1;
            $verification->device_type = $request->device_type;
            $verification->device_id = $request->device_id;
            $verification->otp = $otp;
            $verification->expires_at = $expiryTime;
            $verification->otp_type = 1;
            $verification->save();
        }

        if ($request->type == 'email') {
            try {
                Log::info('Sending OTP email to: ' . $request->value, ['otp' => $otp, 'verification_value' => $verification->value]);
                Mail::to($request->value)->send(new OtpMail($otp));
                return $this->sendResponse($verification->value, __('messages.otp_email_sent'));
            } catch (\Exception $e) {
                Log::error('Failed to send OTP email: ' . $e->getMessage());
                return $this->sendError(__('messages.otp_email_sent_failed_try_again'), [], 500);
            }
        }
    }

    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'value' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (filter_var($value, FILTER_VALIDATE_EMAIL)) return;
                    if (!ctype_digit($value)) {
                        $fail('The value must be a valid email.');
                    }
                },
            ],
            'type' => 'required|in:email',
            'device_type' => 'required|in:1,2',
            'device_id' => 'required',
        ]);

        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();
            return $this->sendError($firstError, [], 422);
        }

        if (!filter_var($request->value, FILTER_VALIDATE_EMAIL)) {
            return $this->sendError(__('messages.invalid_email'), [], 400);
        }

        $email = strtolower($request->value);

        $verification = Verification::where('value', $email)
            ->where('type', 1)
            ->where('device_id', $request->device_id)
            ->first();

        if (!$verification) {
            return $this->sendError(__('messages.no_code_request_found'), [], 404);
        }

        if ($verification->status == 1) {
            return $this->sendError(__('messages.already_verified', ['item' => 'Email']), [], 400);
        }

        $otp = rand(100000, 999999);
        $verification->otp = $otp;
        $verification->expires_at = Carbon::now()->addMinutes(10);
        $verification->otp_type = 1;
        $verification->save();

        if ($request->type === 'email') {
            $this->sendEmail(new OtpMail($otp), $request->value);
        }

        return $this->sendResponse($verification->value, __('messages.opt_resented'));
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'value' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (filter_var($value, FILTER_VALIDATE_EMAIL)) return;
                    if (!ctype_digit($value)) {
                        $fail('The value must be a valid email.');
                    }
                },
            ],
            'type' => 'required|in:email',
            'otp' => 'required',
            'device_type' => 'required|in:1,2',
            'device_id' => 'required',
            'otp_type' => 'required|in:1,2', // 1 = signup, 2 = forgot password
        ]);

        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();
            return $this->sendError($firstError, [], 422);
        }

        if (!filter_var($request->value, FILTER_VALIDATE_EMAIL)) {
            return $this->sendError(__('messages.invalid_email'), [], 400);
        }

        $otpRecord = Verification::where('value', strtolower($request->value))
            ->where('otp_type', $request->otp_type)
            ->where('device_id', $request->device_id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otpRecord) {
            return $this->sendError(__('messages.no_account_no_otp'), [], 400);
        }

        if ($otpRecord->status == 1) {
            return $this->sendError(__('messages.already_verified', ['item' => 'verification code']), [], 400);
        }

        if ($otpRecord->otp !== $request->otp) {
            return $this->sendError(__('messages.otp_invalid'), [], 400);
        }

        if ($otpRecord->expires_at && Carbon::now()->gt($otpRecord->expires_at)) {
            return $this->sendError(__('messages.otp_expired'), [], 400);
        }

        $otpRecord->status = 1;
        $otpRecord->save();

        // Delete all entries for this value with otp_type = 2 after successful verification
        if ((int)$request->otp_type === 2) {
            Verification::where('value', strtolower($request->value))
                ->where('otp_type', 2)
                ->delete();
        }

        return $this->sendResponse($otpRecord->value, __('messages.otp_verified'));
    }


    public function logout(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->sendError(__('messages.unauthenticated', ['item' => 'User']), [], 401);
        }


        $token = $user->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        $user->device_id = null;
        $user->device_type = null;
        $user->save();

        return $this->sendResponse([], __('messages.sign_out_successfully'));
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->sendError(__('messages.incorrect_password'), ['current_password' => [__('messages.incorrect_password')]], 422);
        }

        $user->password = Hash::make($request->new_password);

        if ($user->save()) {
            return $this->sendResponse([], __('messages.update_success', ['item' => 'Password']));
        } else {
            return $this->sendError(__('messages.failed_to_change_password'), [], 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        $all = $request->all();

        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                function ($attribute, $value, $fail) {
                    $value = $value;
                    if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        return;
                    }
                    if (!ctype_digit($value)) {
                        $fail =  $fail('The value must be a valid email .');
                        echo "Here";
                    }
                },
            ],
            'device_type' => 'required|in:1,2',
            'device_id' => 'required',
        ]);

        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();
            return $this->sendError($firstError, [], 422);
        }
        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            return $this->sendError(__('messages.invalid_email'), [], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->sendError(__('messages.email_not_registed'), null, 403);
        }

        if ($user->status == 0) {
            return $this->sendError(__('messages.account_is_deactivate'), null, 403);
        }

        $otp = rand(100000, 999999);
        $expiryTime = Carbon::now()->addMinutes(10);

        $verification = Verification::where('value', strtolower($request->email))
            ->where('type', 1)
            ->where('device_id', $request->device_id)
            ->where('otp_type', 2)
            ->first();

        if ($verification) {
            if ($verification->device_id == $request->device_id) {
                $verification->otp = $otp;
                $verification->expires_at = $expiryTime;
                $verification->otp_type = 2;
                $verification->save();
            } else {
                $verification = new Verification();
                $verification->value = strtolower($request->email);
                $verification->type = 1; //1 = email verify
                $verification->device_type = $request->device_type;
                $verification->device_id = $request->device_id;
                $verification->otp = $otp;
                $verification->expires_at = $expiryTime;
                $verification->otp_type = 2;
                $verification->save();
            }
        } else {
            $verification = new Verification();
            $verification->value = strtolower($request->email);
            $verification->type = 1;
            $verification->device_type = $request->device_type;
            $verification->device_id = $request->device_id;
            $verification->otp = $otp;
            $verification->expires_at = $expiryTime;
            $verification->otp_type = 2;
            $verification->save();
        }

        Mail::to($request->email)->send(new ForgotPassword($otp));
        return $this->sendResponse($verification->email,  __('messages.otp_email_sent'));
    }


    public function showResetForm(Request $request, $token)
    {
        return view('reset_password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }



    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => [
                'required',
                'min:8',
                'max:15',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
            ],
            'device_type' => 'required|in:1,2',
            'device_id' => 'required',
        ], [
            'password.required' => 'The password field is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.max' => 'Password must not exceed 15 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.regex' => 'Password must include uppercase, lowercase, number, and special character.',
        ]);

        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();
            return $this->sendError($firstError, [], 422);
        }

        $user = User::where('email', strtolower($request->email))->first();

        if (!$user) {
            return $this->sendError(__('messages.not_found', ['item' => 'User']), [], 404);
        }

        if ($user->status == 0) {
            return $this->sendError(__('messages.account_is_deactivate'), null, 403);
        }

        // Prevent reusing old password
        if (Hash::check($request->password, $user->password)) {
            return $this->sendError(__('messages.you_cannot_reuse_your_old_password'), [], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return $this->sendResponse([], __('messages.password_reset_successful'));
    }

    public function deleteAccount(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Check if the user is authenticated
        if (!$user) {
            return $this->sendError(__('messages.unauthorized'), [], 403);
        }

        $request->validate([
            'password' => 'nullable|string|min:8|max:20',
        ]);

        if ($request->password && $user->login_type == 1) {
            if (!Hash::check($request->password, $user->password)) {
                return $this->sendError(__('messages.mismatch_password'), null, 403);
            }
        }

        try {
            $user = User::find($user->id);
            // DB::transaction(fn() => $user->delete());
            DB::transaction(function () use ($user) {
                $user->cascadeDelete();
            });
        } catch (\Exception $e) {
            return $this->sendError(__('messages.failed_to_delete_data'), [], 500);
        }

        return $this->sendResponse([], __('messages.delete_success', ['item' => 'Account']));
    }

    public function uploadProfileImage(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:12288',
            'image_partner' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:12288',
        ]);

        if ($request->hasFile('image')) {
            if ($user->profile_photo) {
                Storage::delete($user->profile_photo);
            }

            $userProfilePhotoPath = $this->file_upload(
                $request->file('image'),
                config('constants.uploads') . '/' . $user->id . '/'  . config('constants.user_profile_photo')
            );
            $user->profile_photo = $userProfilePhotoPath['original'];
        }

        if ($request->hasFile('image_partner')) {
            if ($user->partner_profile_photo) {
                Storage::delete($user->partner_profile_photo);
            }

            $partnerProfilePhotoPath = $this->file_upload(
                $request->file('image_partner'),
                config('constants.uploads') . '/' . $user->id . '/'  . config('constants.user_profile_photo')
            );
            $user->partner_profile_photo = $partnerProfilePhotoPath['original'];
        }

        $user->save();

        $response_data = [
            'user_profile_image_url' => $user->profile_photo ? asset($user->profile_photo) : null,
            'partner_profile_image_url' => $user->partner_profile_photo ? asset($user->partner_profile_photo) : null,
        ];

        return response()->json([
            'status' => true,
            'data' => $response_data,
            'message' => __('messages.update_success', ['item' => 'Profile images'])
        ]);
    }

    public function getProfileImage(Request $request)
    {
        $user = $request->user();

        $userProfileImageUrl = $user->profile_photo
            ? asset($user->profile_photo)
            : null;

        $partnerProfileImageUrl = $user->partner_profile_photo
            ? asset($user->partner_profile_photo)
            : null;

        return response()->json([
            'status' => true,
            'data' => [
                'user_profile_image_url' => $userProfileImageUrl,
                'partner_profile_image_url' => $partnerProfileImageUrl,
            ],
            'message' => __('messages.fetche_success', ['item' => 'Profile images'])
        ]);
    }

    public function verifyIdentity(Request $request)
    {
        $user = $request->user();
        $existingIdentity = UserIdentity::where('user_id', $user->id)->first();

        if ($existingIdentity && $existingIdentity->verification_status !== 'rejected') {
            return $this->sendError(
                'You have already submitted identity verification. Please wait for review or update if rejected.',
                [],
                403
            );
        }

        $request->validate([
            'id_type'      => 'required|string',
            'front_of_id'  => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'back_of_id'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        try {
            $uploadBasePath = 'id_verifications/' . $user->id;
            $frontOfIdPath['original']  = $backOfIdPath['original'] = null;
            $uploadBasePath = 'user_identities/' . $user->id; // Folder inside /public
            // Handle front image
            if ($request->hasFile('back_of_id')) {
                $frontOfIdFile = is_array($request->file('front_of_id'))
                    ? $request->file('front_of_id')[0]
                    : $request->file('front_of_id');
                $frontOfIdPath = $this->file_upload($frontOfIdFile, $uploadBasePath);
            }

            if ($request->hasFile('back_of_id')) {
                $backOfIdFile = is_array($request->file('back_of_id'))
                    ? $request->file('back_of_id')[0]
                    : $request->file('back_of_id');

                $backOfIdPath = $this->file_upload($backOfIdFile, $uploadBasePath);
            }

            $userIdentity = UserIdentity::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'id_type'            => $request->id_type,
                    'front_of_id_path'   => $frontOfIdPath['original'],
                    'back_of_id_path'    => $backOfIdPath['original'],
                    'verification_status' => 'pending',
                    'rejection_reason'   => null,
                ]
            );

            return $this->sendResponse([
                'id_verification_status' => $userIdentity->verification_status,
                'front_of_id_url'        => asset($userIdentity->front_of_id_path),
                'back_of_id_url'         => $userIdentity->back_of_id_path ? asset($userIdentity->back_of_id_path) : null,
            ], 'Verification ID submitted. Your identity is under review.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->sendError('Validation Failed', $e->errors(), 422);
        } catch (\Exception $e) {
            Log::error("Identity verification failed for user ID {$user->id}: " . $e->getMessage(), ['exception' => $e]);
            return $this->sendError(__('messages.failed_to_submit_identity'), [], 500);
        }
    }

    public function switchRole(Request $request)
    {
        $user = $request->user(); // Get the authenticated user

        // Admins are not allowed to switch roles
        if ($user->role == self::ROLE_ADMIN) {
            return $this->sendError(__('messages.admin_users_cannot_switch_roles'), [], 403); // Forbidden
        }

        try {
            DB::beginTransaction();

            $roleArray = [
                self::ROLE_ADMIN => 'Admin',
                self::ROLE_TENANT => 'Tenant',
                self::ROLE_OWNER => 'Owner',
            ];

            $currentRole = $user->role;
            $newRole = null;

            switch ($currentRole) {
                case self::ROLE_TENANT:
                    $newRole = self::ROLE_OWNER;
                    break;

                case self::ROLE_OWNER:
                    $newRole = self::ROLE_TENANT;
                    break;
                default:
                    return $this->sendError(__('messages.invalid_role_for_switching'), [], 400);
            }

            // Update the user's role
            $user->role = $newRole;
            $user->save();
            $user->refresh();

            DB::commit();
            $newRoleName = $roleArray[$user->role] ?? 'Tenant';

            $message = [
                'Tenant' => '🔍 🔄Switched to Room Seeker Mode',
                'Owner' => '🏠🔄 Switched to Listing Owner Portal'
            ];

            return ApiResponse::success([
                'new_role_id' => $user->role,
                'new_role_name' => $newRoleName,
            ], $message[$newRoleName]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Role switch failed for user ID: {$user->id}. Error: {$e->getMessage()}");
            return $this->sendError(__('messages.failed_to_switch_role'), [], 500);
        }
    }


    public function getIdentityStatus(Request $request)
    {
        $user = $request->user();

        if (!$user->userIdentity) {
            return response()->json([
                'success' => true,
                'status' => 'not_submitted',
                'message' => 'No identity submitted yet.',
            ]);
        }

        return response()->json([
            'success' => true,
            'status' => $user->userIdentity->verification_status,
            'verified_at' => $user->userIdentity->verified_at,
        ]);
    }

    public function getProfileIdentity(Request $request)
    {
        $user = $request->user();

        $identity = UserIdentity::where('user_id', $user->id)->first();

        if (!$identity) {
            return response()->json([
                'success' => false,
                'message' => 'Identity not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'id_type' => $identity->id_type,
            'front_of_id_path' => asset($identity->front_of_id_path),
            'back_of_id_path' => $identity->back_of_id_path ? asset($identity->back_of_id_path) : null,
            'verification_status' => $identity->verification_status,
            'rejection_reason' => $identity->rejection_reason,
            'verified_at' => $identity->verified_at,
            'created_at' => $identity->created_at,
            'updated_at' => $identity->updated_at,
        ]);
    }

    public function locationUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'data'    => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = $request->user();
        if (!$user) {
            return $this->sendError(__('messages.invalid_credentials'), null, 401);;
        }

        $disk = config('constants.file_upload_location');

        // if($disk === 'local' || $disk === 'public' ){
        if (false) {
            $user->update([
                'latitude' => fake()->latitude,
                'longitude' => fake()->longitude,
            ]);
        } else {
            $user->update([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.success_msg', ['item' => 'User location details are updated']),
        ]);
    }

    public function blockUser(Request $req)
    {
        $currentUser = $req->user();

        $validator = Validator::make($req->all(), [
            'user_id' => 'required|numeric|exists:' . User::class . ',id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        if ($currentUser->id == $req->user_id) {
            return ApiResponse::notFound(__('messages.not_found', ['item' => 'User']));
        }

        $tenant = User::find($req->user_id);
        if (!$tenant?->id) {
            return ApiResponse::notFound(__('messages.not_found', ['item' => 'Tenant']));
        }

        $tenantId = $tenant->id;

        if ($currentUser->hasBlocked($tenantId)) {
            $currentUser->blockedUsers()->detach($tenantId);
            return ApiResponse::success(__('messages.success_msg', ['item' => 'User unblocked ']));
        }
        $currentUser->blockedUsers()->attach($tenantId);
        return ApiResponse::success(__('messages.success_msg', ['item' => 'User blocked']));
    }

    public function blockUserList(Request $request)
    {
        $currentUser = $request->user();
        $perPage = $request->input("per_page", config('constants.per_page', 10));
        $isPaginate = filter_var($request->input('is_paginate', 'true'), FILTER_VALIDATE_BOOLEAN);
        $query = $currentUser->blockedUsers();
        if ($isPaginate) {
            $users = $query->paginate($perPage);
            return ApiResponse::paginate($users, BlockUserResource::collection($users));
        }

        $users = $query->get();
        return ApiResponse::success(BlockUserResource::collection($users));
    }
}
