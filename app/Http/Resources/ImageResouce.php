<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResouce extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'path' => $this->path ? asset($this->path) : null,
            'type' => $this->type === 0 ? 'image' : 'video',
            'thumbnail_path' => $this->thumbnail_path ? asset($this->thumbnail_path) : null,
        ];
    }
}