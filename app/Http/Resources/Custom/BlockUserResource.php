<?php

namespace App\Http\Resources\Custom;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlockUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'profile_photo' => $this->profile_photo ? asset($this->profile_photo) : null,
            'block_time' => optional($this->pivot)->created_at?->format('Y-m-d H:i:s'),
        ];

        return $data;
    }
}
