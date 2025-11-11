<?php

use App\Http\Controllers\admin\AdminAuthController;
use App\Http\Controllers\admin\AdminUserController;
use App\Http\Controllers\admin\AdminDashboardController;
use App\Mail\ForgotPasswordMail;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/privacy-policy', function () {
    return view('privacy_policy');
});
Route::get('/terms&condition', function () {
    return view('terms_condition');
});

Route::get('/email', function () {
    $otp = '123456';
    $name = 'test';
    return new ForgotPasswordMail($otp, $name);
});

Route::get('/clear-cache', function () {
    Artisan::call('optimize');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    return response()->json(['message' => 'Application cache cleared']);
});

Route::prefix('.well-known')->group(function () {
    Route::get('apple-app-site-association', function () {
        $path = storage_path('app/.well-known/apple-app-site-association.json');
        if (file_exists($path)) {
            return response()->file($path, [
                'Content-Type' => 'text/plain',
            ]);
        }   
        return response()->json(['error' => 'File not found.'], 404);
    });
    Route::get('assetlinks', function () {
        $path = storage_path('app/.well-known/assetlinks.json');
        if (file_exists($path)) {
            return response()->file($path, [
                'Content-Type' => 'text/plain',
            ]);
        }
        return response()->json(['error' => 'File not found.'], 404);
    });
});

Route::get('/redirect-url/{type?}', [AdminAuthController::class, 'redirectFunction'])->where('type', '.*')->name('redirect-url');

Route::get('/appCourses', function () {
    // redirect to the appCourses page
    return redirect()->to('https://apps.apple.com/us/app/ziloo-app/id6742143076');
});
 
Route::get('/appCourses{any?}', function () {
    return redirect()->to('https://apps.apple.com/us/app/ziloo-app/id6742143076');
})->where('any', '.*');
 
 

// ---------------------------------- ADMIN ----------------------------------------------

Route::prefix('admin')->name('admin.')->middleware(['guest:admin'])->group(function () {

    Route::view('/login', 'admin.auth.login')->name('login');
    Route::view('/forgot-password', 'admin.auth.forgot-password')->name('forgotPassword');

    Route::controller(AdminAuthController::class)->group(function () {
        Route::post('login', 'loginAuth')->name('loginAuth');
        Route::post('/forgot-password', 'sendResetToken')->name('sendResetToken');
        Route::post('/password/reset', 'passwordReset')->name('passwordReset');
        Route::get('/password/reset/{token}', 'showResetForm')->name('showResetForm');
        Route::post('/password/reset/{token}', 'resetPassword')->name('resetPassword');

        Route::get('/otp-verify', 'otpForm')->name('otpForm');
        Route::post('otp-verify', 'verifyOtp')->name('verifyOtp');
        Route::post('resend-otp', 'resendOtp')->name('resendOtp');
    });
});

Route::prefix('admin')->name('admin.')->middleware(['auth:admin'])->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('logout', [AdminAuthController::class, 'logout'])->name('logout');
    Route::view('/profile', 'admin.profile.index')->name('getProfile');

    Route::controller(AdminDashboardController::class)->group(function () {
        Route::post('update-status',  'updateStatus')->name('updateStatus'); //ACTIVE INACTIVE
        Route::get('/change-password', function () {
            return view('change-password');
        });
        Route::post('/change-password', 'changePassword')->name('changePassword');
    });

    Route::prefix('users')->controller(AdminUserController::class)->group(function () {
        Route::get('/', 'index')->name('userIndex');
        Route::get('/{userId}', 'userDetails')->name('userdetails');
        Route::post('add', 'userSave')->name('userSave');
        Route::get('edit/{userId}', 'userEdit')->name('userEdit');
        Route::post('update/{userId}', 'userUpdate')->name('userUpdate');

        Route::post('/{user}/verify-identity', 'verifyIdentity')->name('verifyIdentity');
    });
});

Route::prefix('admin')->group(function () {
    Route::get('/', function () {
        if (auth('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('admin.login');
    });

    Route::any('{any}', function () {
        if (auth('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('admin.login');
    })->where('any', '.*');
});

Route::get('/contact-us', function () {
    return view('contact_us');
})->name('contactUs');

Route::get('{any}', function () {
    return view('welcome');
})->where('any', '.*');