<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\api\v1\BaseApiController;
use App\Http\Resources\NotificationResource;
use App\Traits\Common_trait;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Validator;
use App\Services\Notifications\NotificationService;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ChatController extends BaseApiController
{
    use Common_trait;

    public function uploadFile(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'file' => 'nullable|file|max:51200',
        ]);

        if ($request->hasFile('file')) {
            $userProfilePhotoPath = $this->file_upload(
                $request->file('file'),
                config('constants.uploads') . '/' . $user->id . '/chat'
            );
            return ApiResponse::success(($userProfilePhotoPath['original']) ? asset($userProfilePhotoPath['original']) : '');
        }
        return ApiResponse::forbidden(__('messages.something_went_wrong'));
    }

    public function chatNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiverId' => 'required|exists:' . User::class . ',id',
            'message'    => 'required|string',
            'chatId'     => 'required|string',
        ]);

        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();
            return ApiResponse::error($firstError, 422);
        }
        $sender = $request->user();
        try {
            $newChat = new Chat();
            $newChat->sender_id = $sender->id;
            $newChat->receiver_id = $request->receiverId;
            $newChat->message = $request->message;
            $newChat->chat_id = $request->chatId;
            $newChat->save();
            $receiver = User::findOrFail($request->receiverId);
            $shouldSendNotification = Chat::shouldNotify(
                $request->chatId,
                $sender->id,
                $receiver->id
            );
            $notification = null;
            if ($shouldSendNotification) {
                $notification = app(NotificationService::class)->send(
                    type: 'chat',
                    id: $receiver->id,
                    name: $sender->name,
                    image: $sender->image,
                    message: $request->message,
                    meta: $request->only('receiverId', 'message', 'chatId'),
                    senderId: $sender->id
                );
            }
            if ($notification) {
                return ApiResponse::success(new NotificationResource($notification), 'chat send successfully');
            }
        } catch (\Exception $e) {
            $error = $e->getMessage() . ' - ' . $e->getLine();
            return ApiResponse::success([], $error);
        }
    }

    public function removeActiveChat(Request $request)
    {
        $user = $request->user();
        Cache::put("active_chat_user_" . $user->id, null);
        return ApiResponse::success([], 'Active chat removed successfully');
    }


    public function setActiveChat(Request $request)
    {
        $request->validate(['chatId' => 'required|string']);
        $user = $request->user();
        Cache::put("active_chat_user_" . $user->id, $request->chatId);
        return ApiResponse::success([], 'Active chat set successfully');
    }

    public function getChatCount(Request $request)
    {
        $user = $request->user();

        return ApiResponse::success([
            'is_subscribed' => $user->is_subscribed,
            'chat_count' => $user->chat_count,
        ]);
    }

    public function reduceChatCount(Request $request)
    {
        $user = $request->user();

        if ($user->chat_count <= 0) {
            return ApiResponse::error('No chat count available', 400);
        }

        $user->decrement('chat_count', 1);

        return ApiResponse::success([
            'chat_count' => $user->chat_count,
        ]);
    }
}
