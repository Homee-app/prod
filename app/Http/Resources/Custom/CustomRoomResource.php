<?php

namespace App\Http\Resources\Custom;

use App\Http\Resources\ImageResouce;
use App\Http\Resources\Custom\CustomPropertyResource;
use App\Traits\Common_trait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomRoomResource extends JsonResource
{
    use Common_trait;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $preamps = "roomId=" . $this->id . "&propertyId=" . $this->property_id;
        $authUser = auth()?->user();
        $percentage = $authUser?->id ? findUserPercentage($authUser->id) : null;
        $profile_completed = false;
        if ($percentage) {
            $profile_completed = $percentage['is_completed'] ?? false;
        }

        $data = [
            "id" => $this->id,
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'last_updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];

        $data['is_saved'] = $this?->is_saved ?? false;
        $data['share_url'] = app(\App\Services\DeepLinkService::class)->createDeepLink('room', $preamps) ?? [];

        if ($this->questionsanswer != null) {
            $data['question_answer'] = $this->makeQandA($this->whenLoaded('questionsanswer'));
        }

        $data['image'] = ImageResouce::collection($this->whenLoaded('images'));
        $data['property'] = new CustomPropertyResource($this->whenLoaded('property'));

        $data['property_status'] = $authUser?->can_add_property;
        $data['room_status'] = $authUser?->can_add_room;
        $data['allowed_chat'] = $authUser?->can_chat;
        $data['profile_completed'] =  $profile_completed;

        return $data;
    }
}
