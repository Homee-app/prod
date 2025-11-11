<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'label' => trim($this->label_for_app),
            'value' => $this?->value,
            'min_val' => $this?->min_val,
            'max_val' => $this?->max_val,
            'question_id' => $this?->question_id,
        ];

        if($this?->image){
            $data['image'] = $this->image ? asset($this->image) : null;
        }

        return $data;
    }
}
