<?php

namespace App\Http\Resources;

use App\Http\Resources\Custom\CustomPropertyResource;
use App\Traits\Common_trait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HousemateResource extends JsonResource
{
    use Common_trait;
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
            'status' => $this->status,
            'image' => ImageResouce::collection($this->whenLoaded('images')),
        ];
        
        if($this?->questionsanswer != null) {
            $data['question_answer'] = $this->makeQandA($this->whenLoaded('questionsanswer'));
        }

        $data['property'] =  new CustomPropertyResource($this->whenLoaded('property'));

        return $data;
    }
}
