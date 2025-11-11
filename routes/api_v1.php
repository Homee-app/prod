<?php

use App\Http\Controllers\api\v1\ApiSubscriptionController;
use App\Http\Controllers\api\v1\ChatController;
use App\Http\Controllers\api\v1\ApiAuthController;
use App\Http\Controllers\api\v1\ExploreController;
use App\Http\Controllers\api\v1\HousemateController;
use App\Http\Controllers\api\v1\ImageController;
use App\Http\Controllers\api\v1\PropertyController;
use App\Http\Controllers\api\v1\ShareController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\v1\NotificationController;
use App\Http\Controllers\api\v1\QuestionController;
use App\Http\Controllers\api\v1\RoomController;
use App\Http\Controllers\api\v1\SuburbController;
use App\Http\Controllers\api\v1\TenantController;
use App\Http\Controllers\api\v1\SocialLoginController;

// Api Routes
//->middleware(['ApiAuthMiddleware:admin']) // Middleware after prefix

Route::post('checkEmail', [ApiAuthController::class, 'checkEmail']);
Route::post('send-push', [NotificationController::class, 'sendPushNotification']);
Route::post('social-login', [SocialLoginController::class, 'socialLogin']);

Route::controller(ApiAuthController::class)->group(function () {
    Route::post('signup', 'signup');
    Route::post('login', 'login')->name('login');
    Route::post('forgot-password', 'forgotPassword');
    Route::post('send-otp', 'sendOtpVerification');
    Route::post('resend-otp', 'resendOtp');
    Route::post('verify-otp', 'verifyOtp');
    Route::post('reset-password', 'resetPassword')->name('password.reset');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::controller(ApiAuthController::class)->group(function () {
        Route::get('logout', 'logout');
        Route::delete('delete-account', 'deleteAccount');
        Route::post('change-password', 'changePassword');
        Route::post('user/switch-role', 'switchRole')->name('api.user.switch_role');
        Route::post('user/location',  'locationUpdate');
        Route::post('block-user', 'blockUser');
        Route::get('block-user-list', 'blockUserList');
    });
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('questions', [QuestionController::class, 'index']);
    Route::post('questionsanswer', [QuestionController::class, 'store']);
    Route::get('suburbs', [SuburbController::class, 'getAll']);
    Route::get('suburbs/search', [SuburbController::class, 'search']);
    Route::post('suburbs/by-ids', [SuburbController::class, 'getByIds']);
    Route::get('questions-by-screen/{screen_id}', [QuestionController::class, 'getByScreen']);
    Route::get('questions/grouped-by-screen', [QuestionController::class, 'getGroupedByScreen']);
    Route::get('questions/profile-status', [QuestionController::class, 'profileStatus']);
    Route::post('profile/upload-image', [ApiAuthController::class, 'uploadProfileImage']);
    Route::get('profile/image', [ApiAuthController::class, 'getProfileImage']);
    Route::get('profile-percentage', [QuestionController::class, 'profilePercentage']);
    Route::post('verify-identity', [ApiAuthController::class, 'verifyIdentity']);
    Route::get('tenants/lifestyle-match/{viewed_user_id}', [TenantController::class, 'getLifestyleMatch']);
    Route::post('nearby-tenants', [TenantController::class, 'getNearbyTenants']);
    Route::get('tenants-filter', [QuestionController::class, 'tenantsFilter']);
    Route::get('identity-status', [ApiAuthController::class, 'getIdentityStatus']);
    Route::get('tenant-details/{id}', [TenantController::class, 'getTenantDetails']);
    Route::post('save-tenant', [TenantController::class, 'toggleLike']);
    Route::get('profile/identity', [ApiAuthController::class, 'getProfileIdentity']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    // property resource
    Route::resource('property', PropertyController::class);
    Route::controller(PropertyController::class)->prefix('property/{propertyId}')->group(function () {
        // property status
        Route::post('status-update', 'statusUpdate');
        // group by property questions
        Route::get('edit-property', 'getGroupedByDetails');
        // property room
        Route::resource('rooms', RoomController::class);
        Route::controller(RoomController::class)->prefix('rooms/{roomId}')->group(function () {
            // property room status
            Route::post('status-update', 'statusUpdate');
            Route::get('edit-room', 'getGroupedByDetails');
            Route::get('room-images', 'getRoomImages');
        });
        // housemates
        Route::resource('housemates', HousemateController::class);
        Route::controller(HousemateController::class)->prefix('housemates/{housemateId}')->group(function () {
            // property housemates status
            Route::post('status-update', 'statusUpdate');
        });
    });
    Route::controller(ExploreController::class)->prefix('explore')->group(function () {
        Route::post('rooms', 'roomsListing');
        Route::post('save-list', 'savelisting');
        Route::get('filter', 'filter');
    });

    Route::delete('images/{id}', [ImageController::class, 'destroy']);
    Route::post('save-room', [RoomController::class, 'toggleLike']);
    Route::controller(ChatController::class)->prefix('chat')->group(function () {
        Route::post('upload-file', 'uploadFile');
        Route::post('notify', 'chatNotification');
        Route::post('remove-chat', 'removeActiveChat');
        Route::post('start-chat', 'setActiveChat');
        Route::get('get-chat-count', 'getChatCount');
        Route::post('reduce-chat-count', 'reduceChatCount');
    });

    Route::controller(NotificationController::class)->prefix('notifications')->group(function () {
        Route::get('', 'index');
        Route::post('{id}/read', 'markAsRead');
        Route::post('read-all', 'markAllAsRead');
        Route::get('read-status', 'readStatus');
        Route::get('{id}/new-rooms', 'notifyRooms');
    });
});

Route::post('saveNearbyForProperty', [PropertyController::class, 'saveNearbyForProperty']);

// ------------------ Share Urls ----------------//
Route::controller(ShareController::class)->name('share')->prefix('share/{id}')->group(function () {
    Route::get('room', 'room')->name('room');
    Route::get('tetant', 'tetant')->name('tetant');
});

Route::prefix('purchase')->middleware('auth:sanctum')->controller(ApiSubscriptionController::class)->group(function () {
    Route::post('buy', 'buyProducts');
    Route::post('use-boost', 'useBoost');
    Route::post('use-key', 'useKey');
    Route::post('use-room-boost', 'useRoomBoost');
    Route::post('add-key', 'addKey');
    Route::get('get-key-count', 'getKeyCount');
    Route::get('resources/{type?}', 'resourcePage');
    Route::get('current-plan', 'currentPlan');
});

Route::fallback(function (Request $request) {
    if ($request->expectsJson() || $request->is('api/*')) { // Important check
        return response()->json(['error' => 'Route Not Found.'], 404);
    }
});
