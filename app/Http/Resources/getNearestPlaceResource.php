<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\NearbyPlace;

class getNearestPlaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $icon = NearbyPlace::TYPE_ICONS[$this->type] ?? null;
        return [
            'type' => $this->type,
            'distance' => $this->distance_text,
            'duration' => $this->duration_text,
            'icon' => $icon ? asset($icon) : null,
        ];
    }
}
