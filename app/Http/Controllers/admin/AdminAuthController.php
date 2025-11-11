<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Mail\ForgotPasswordMail;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Traits\Common_trait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminTwoFactorCodeMail;


class AdminAuthController extends Controller
{
    use  Common_trait;
    public function loginAuth(Request $req)
    {
        $req->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = [
            'email' => $req->input('email'),
            'password' => $req->input('password'),
            'role' => 1
        ];

        if (Auth::guard('admin')->validate($credentials)) {
            $user = User::where('email', $req->input('email'))->where('role', 1)->first();

            // Generate OTP
            $otp = rand(100000, 999999);
            $user->otp_code = $otp;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();

            // Send OTP via email
            Mail::to($user->email)->send(new AdminTwoFactorCodeMail($user));

            // Store for verification
            session([
                'admin_2fa_email' => $user->email,
                'admin_otp_sent_at' => now()->timestamp, // Store OTP sent time
            ]);

            return redirect()->route('admin.otpForm');
        }

        return back()->with('flash-error', __('messages.invalid_credentials'))->withInput();
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login')->with('flash-success', __('messages.logout'));
    }

    public function sendResetToken(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email not found']);
        }

        $token = bin2hex(random_bytes(16));
        $createdAt = Carbon::now();

        // Save token
        PasswordResetToken::updateOrCreate(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => $createdAt]
        );

        // Send email
        $resetLink = url('/admin/password/reset/' . $token);

        // MAIL CODE
        $aa =  $this->sendEmail(new ForgotPasswordMail($resetLink, $user->first_name), $request->email);

        pre($resetLink, 's');
        pre($aa);

        return back()->with('flash-success', __('messages.send_password_reset_link'));
    }

    public function showResetForm($token)
    {
        $tokenData = PasswordResetToken::where('token', $token)->first();
        if (!$tokenData || Carbon::parse($tokenData->created_at)->addMinutes(15)->isPast()) {
            return redirect()->route('admin.forgotPassword')->with('flash-error', __('messages.invalid_expired_token'));
        }

        return view('admin.auth.reset-password', ['token' => $token]);
    }

    // Handle password update
    public function resetPassword(Request $request)
    {
        $request->validate([
            'new_password' => 'required|confirmed|min:6|max:15',
        ]);

        $tokenData = PasswordResetToken::where('token', $request->token)->first();

        if (!$tokenData || Carbon::parse($tokenData->created_at)->addMinutes(15)->isPast()) {
            return redirect()->route('admin.forgotPassword')->with('flash-error', __('messages.invalid_expired_token'));
        }

        $user = User::where('email', $tokenData->email)->first();

        if (!$user) {
            return redirect('/password/request')->withErrors(['email' => 'User not found']);
        }

        $user->password = Hash::make($request->new_password);

        if ($user->save()) {
            $tokenData->delete();
        }
        return redirect()->route('admin.forgotPassword')->with('flash-error', __('messages.something_error'));
    }


    public function otpForm()
    {
        if (!session('admin_2fa_email')) {
            return redirect()->route('admin.loginForm');
        }
        return view('admin.auth.otp_verify');
    }

    public function verifyOtp(Request $req)
    {
        // Combine the OTP array into a single string
        $otp = is_array($req->otp) ? implode('', $req->otp) : $req->otp;

        // Validate OTP
        $req->merge(['otp_combined' => $otp]); // merge into request for validation
        $req->validate([
            'otp_combined' => 'required|digits:6',
        ], [
            'otp_combined.required' => 'Verification code is required.',
            'otp_combined.digits' => 'Verification code must be 6 digits.',
        ]);

        // Retrieve user based on session email
        $user = User::where('email', session('admin_2fa_email'))->first();

        if (!$user) {
            return redirect()->route('admin.login')->with('flash-error', __('messages.login_again'));
        }

        if ($user->otp_code !== $otp) {
            return back()->with('flash-error', __('messages.otp_invalid'))->withInput();
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            return back()->with('flash-error', __('messages.otp_expired'))->withInput();
        }

        // OTP verified successfully
        session()->forget('admin_2fa_email');
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        Auth::guard('admin')->login($user);

        return redirect()->route('admin.dashboard')->with('flash-success', __('messages.login'));
    }

    public function resendOtp(Request $req)
    {
        $email = session('admin_2fa_email');

        if (!$email) {
            return response()->json(['status' => false, 'message' => 'Session expired. Please login again.'], 419);
        }

        $user = User::where('email', $email)->where('role', 1)->first();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Admin user not found.'], 404);
        }

        // Generate new OTP
        $otp = rand(100000, 999999);
        $user->otp_code = $otp;
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();

        // Send mail
        Mail::to($user->email)->send(new AdminTwoFactorCodeMail($user));

        return response()->json(['status' => true, 'message' => __('messages.otp_sent')]);
    }

    public function redirectFunction($type)
    {
        switch ($type) {
            case 'android':
                $url = app(\App\Services\DeepLinkService::class)->redirectFunctionGoogle();
                break;
            case 'apple':
                $url = app(\App\Services\DeepLinkService::class)->redirectFunctionApple();
                break;
            default:
                $url = '/';
        }
        return response()->view('redirect-delay', compact('url'));
    }
}
