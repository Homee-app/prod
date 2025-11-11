<?php

namespace App\Http\Resources\Custom;

use App\Http\Resources\ImageResouce;
use App\Traits\Common_trait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class CustomPropertyResource extends JsonResource
{
    use Common_trait;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $authUser = auth()?->user();

        $data = [
            'id' => $this->id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status == '1' ? 1 : 0,
            'created_at' => $this?->created_at ? $this?->created_at->format('Y-m-d H:i:s') : null,
        ];

        if ($this->questionsanswer) {
            $data['question_answer'] = $this->makeQandA($this->whenLoaded('questionsanswer'));
        }

        if ($this->room_image) {
            $data['room']['count'] = $this->rooms_count ?? 0;
            $data['room']['rent'] = $this->room_rent ?? 0;
            $data['room']['image'] = asset($this->room_image);
        }

        if ($this->housemates_count > 0) {
            $data['housemate']['count'] = $this->housemates_count ?? 0;
            $data['housemate']['images'] = $this->housemate_images ? ImageResouce::collection($this->housemate_images) : [];
        }
        $data['property_status'] = $authUser?->can_add_property;
        $data['room_status'] = $authUser?->can_add_room;
        $data['allowed_chat'] = $authUser?->can_chat;
        return $data;
    }
}
