<?php

namespace App\Http\Resources;

use App\Traits\Common_trait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResouce extends JsonResource
{
    use Common_trait;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $ownerRelation = $this->whenLoaded('property_owner') ?? null;
        $roomCount = $this->rooms_count ?? 0;
        $houemateCount = $this->housemates_count ?? 0;
        $authUser = auth()?->user();

        $data = [
            'id' => $this->id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];

        if ($this->questionsanswer != null) {
            $data['question_answer'] = $this->makeQandA($this->whenLoaded('questionsanswer'));
        }

        if ($this->rooms?->count('id') > 0) {
            $data['rooms'] =  RoomResouce::collection($this->whenLoaded('rooms'));
            $data['rooms_count'] = $roomCount;
        }

        if ($this->housemates?->count('id') > 0) {
            $data['housemates'] = HousemateResource::collection($this->whenLoaded('housemates'));
            $data['housemates_count'] = $houemateCount;
        }

        if ($this->owner_id && $ownerRelation instanceof \Illuminate\Database\Eloquent\Model && $ownerRelation?->relationLoaded('user')) {
            $data['owner'] = new PropertyOwnerResource($ownerRelation->user);
        }

        if ($this?->nearbyPlaces != null) {
            $data['nearby_place'] = getNearestPlaceResource::collection($this->whenLoaded('nearbyPlaces'));
        }

        $data['property_status'] = $authUser?->can_add_property;
        $data['room_status'] = $authUser?->can_add_room;
        $data['allowed_chat'] = $authUser?->can_chat;

        return $data;
    }
}
