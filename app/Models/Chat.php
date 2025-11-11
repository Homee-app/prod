<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Chat extends Model
{
    //

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.chats');
    }

    public static function shouldNotify($chatId, $senderId, $receiverId): bool
    {
        // Check active chats for both users
        $receiverActiveChatId = Cache::get("active_chat_user_" . $receiverId);
        $senderActiveChatId   = Cache::get("active_chat_user_" . $senderId);

        // Case 1: Both are in the same chat → no notification
        if ($receiverActiveChatId == $chatId && $senderActiveChatId == $chatId) {
            return false;
        }

        // Case 2: Receiver is in this chat → no notification
        if ($receiverActiveChatId == $chatId) {
            return false;
        }

        // Case 3: In all other cases → notify
        return true;
    }
}
