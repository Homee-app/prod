<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $fcmUrl;
    protected $serverKey;
    protected $url;

    public function __construct()
    {
        $this->fcmUrl = config('services.fcm.base_url');
        $this->serverKey = config('services.fcm.server_key');
        $this->url = 'https://oauth2.googleapis.com/token';
    }

    public function create($receiverId, $type, $title, $message, $thumbnail = null, $meta = [], $senderId = null): Bool|Notification
    {
        try {
            $receiver = User::find($receiverId);
            $sender = Auth::user();

            $meta['notification_type'] = $type;
            $meta['receiverId'] =  (string) $receiver->id;

            if ($sender?->id) {
                $meta['senderId'] = (string) $sender->id;
                $meta['senderName'] = $sender?->name ?? '';
                $meta['senderImage'] = $sender?->image ?? '';
                $meta['notification_id'] = '';
            } else {
                $meta['senderId'] = "";
                $meta['senderName'] = "";
                $meta['senderImage'] = "";
            }

            $notification = Notification::create([
                'user_id'   => $receiver->id,
                'type'      => $type,
                'title'     => $title ?? $message,
                'message'   => $message,
                'meta'      => $meta,
                'thumbnail' => $thumbnail,
            ]);

            $notification->meta = array_merge($notification->meta ?? [], [
                'notification_id' => (string) $notification->id,
            ]);
            $notification->save();
            $this->sendPush($receiver->device_id, $title, $message, $notification->meta);
            return $notification;
        } catch (\Exception $e) {
            Log::error("Error in NotificationService::create: " . $e->getMessage());
            return false;
        }
    }

    public function sendPush($deviceToken, $title, $body, $data = [])
    {
        try {
            $payload = [
                'message' => [
                    'token' => $deviceToken,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data' =>  $data,
                ]
            ];
            sleep(1);
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getAccessToken($this->serverKey),
                'Content-Type'  => 'application/json',
            ])
                ->withOptions(["verify" => false])
                ->post($this->fcmUrl, $payload)
                ->json();
            Log::info('FCM response:', $response);
            return $response;
        } catch (\Exception $e) {
            $error = $e->getMessage() . ' - ' . $e->getLine();
            Log::error($error);
        }
    }

    protected function getAccessToken($serverKey)
    {;
        $key = storage_path('app/notification/homee-77e71-firebase-adminsdk-fbsvc-f9163584dc.json');
        $credentials = json_decode(file_get_contents($key), true);
        $data = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $this->generateJwtAssertion($credentials),
        ];
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        $tokenData = json_decode($result, true);
        return $tokenData['access_token'];
    }

    protected function generateJwtAssertion($credentials)
    {
        $now = time();
        $expires = $now + 3600;
        $jwtHeader = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];
        $jwtPayload = [
            'iss' => $credentials['client_email'],
            'sub' => $credentials['client_email'],
            'aud' => 'https://oauth2.googleapis.com/token',
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'iat' => $now,
            'exp' => $expires,
        ];
        $encodedHeader  = rtrim(strtr(base64_encode(json_encode($jwtHeader)), '+/', '-_'), '=');
        $encodedPayload = rtrim(strtr(base64_encode(json_encode($jwtPayload)), '+/', '-_'), '=');
        $dataToSign = $encodedHeader . '.' . $encodedPayload;
        $signature = '';
        openssl_sign($dataToSign, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256);
        $encodedSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        return $dataToSign . '.' . $encodedSignature;
    }

    public function send($type, $id, $name, $image = null, $message = null, $meta = [], $senderId = null): bool|Notification
    {
        $return = false;
        sleep(1);
        $templates = [
            'room_price_update' => [
                'title'   => 'Room pricing change',
                'message' => "Heads up 👀 The price for :name just got updated.",
            ],
            'room_detail_update' => [
                'title'   => 'Room detail update',
                'message' => "📝 New details have been added to :name.",
            ],
            'room_status_update' => [
                'title'   => 'Room status change',
                'message' => "🚫 :name is no longer available.",
            ],
        ];
        if ($type === 'chat') {
            return $this->create(receiverId: $id, type: $type, title: $name, message: $message, thumbnail: $image, meta: $meta, senderId: $senderId);
        }
        if ($type === 'new_property') {
            $subscriped = User::find($id);
            if ($subscriped->is_subscribed == 1) {
                return $this->create(receiverId: $id, type: $type, title: $name, message: $message, thumbnail: $image, meta: $meta, senderId: $senderId);
            }
        }
        if ($type === 'user_identity_verified' || $type === 'user_identity_rejected' || $type == 'user_identity') {
            return $this->create(receiverId: $id, type: $type, title: $name, message: $message, thumbnail: $image, meta: $meta, senderId: $senderId);
        }
        if (isset($templates[$type])) {
            $template = $templates[$type];
            $title   = $template['title'];
            $message = str_replace(':name', $name, $template['message']);
            $users = DB::table(config('tables.tenant_likes'))
                ->whereRoomId($id)
                ->pluck('tenant_id')
                ->toArray();
            $roomDetail = Room::whereId($id)->first();
            Log::info("roomDetail:", (array) $roomDetail);
            foreach ($users as $userId) {
                $subscriped = User::find($userId);
                if ($subscriped->is_subscribed == 1) {
                    $this->create(
                        receiverId: $userId,
                        type: $type,
                        title: $title,
                        message: $message,
                        thumbnail: $image,
                        meta: [
                            'roomId' => (string)  $id,
                            'message' => $message,
                            'propertyId' =>  (string) ($roomDetail->property_id ?? 0)
                        ],
                        senderId: $senderId
                    );
                }
            }
            $return = true;
        }
        return $return;
    }
}
