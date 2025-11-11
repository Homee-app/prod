<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\ApiResponse;
use App\Http\Resources\Custom\CustomRoomResource;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;
use App\Http\Controllers\api\v1\BaseApiController;
use App\Models\Notification;
use App\Models\Room;

class NotificationController extends BaseApiController
{

    public function index(Request $request)
    {
        $perPage = $request->input("per_page", config('constants.per_page', 10));
        $isPaginate = filter_var($request->input('is_paginate', 'true'), FILTER_VALIDATE_BOOLEAN);

        $query = Notification::where('user_id', $request->user()->id)->where('type', '!=', 'chat')->latest();

        if ($isPaginate) {
            $notifications = $query->paginate($perPage);
            return ApiResponse::paginate($notifications, NotificationResource::collection($notifications));
        }

        $notifications = $query->get();
        return ApiResponse::success(NotificationResource::collection($notifications));
    }

    // Mark notification as read
    public function markAsRead($id)
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return ApiResponse::notFound(__('messages.not_found', ['item' => 'Notification']));
        }

        if ($notification->read_at) {
            $type = 'unread';
            $notification->markAsUnRead();
        } else {
            $type = 'read';
            $notification->markAsRead();
        }
        return ApiResponse::success(new NotificationResource($notification), "chat $type successfully");
    }

    // Mark all notifications as read
    public function markAllAsRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true, 'message' => 'all chat readed successfully']);
    }

    public function readStatus(Request $request)
    {
        $statusCount = Notification::where('user_id', $request->user()->id)
            ->whereNotIn('type', ['chat'])
            ->whereNull('read_at')
            ->count();

        return response()->json(['status' => ($statusCount > 0) ? true : false]);
    }


    public function notifyRooms(Request $request, $id)
    {
        $perPage = $request->input("per_page", config('constants.per_page', 10));
        $isPaginate = filter_var($request->input('is_paginate', 'true'), FILTER_VALIDATE_BOOLEAN);
        $notification  = Notification::whereId($id)->first();

        if (!$notification) {
            return ApiResponse::notFound(__('messages.not_found', ['item' => 'Notification']));
        }

        // Decode meta as array
        $roomsIs = $notification?->meta['roomIds'] ?? [];
        
        if ($roomsIs = json_decode($roomsIs, true)) {
            $query = Room::with(['images', 'questionsanswer', 'property' => fn($q) => $q->whereStatus('1')])->whereIn('id', $roomsIs)->whereStatus('1');
            if ($isPaginate) {
                $rooms = $query->paginate($perPage);
                return ApiResponse::paginate($rooms, CustomRoomResource::collection($rooms));
            }
            $rooms = $query->get();
            return ApiResponse::success(CustomRoomResource::collection($rooms));
        } else {
            return ApiResponse::success([], __('messages.not_found', ['item' => 'Rooms']));
        }
    }
}
