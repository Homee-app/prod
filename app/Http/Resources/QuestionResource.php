<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Suburb;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $response =  [
            'question_id' => $this->id,
            'title' => $this->title_for_app,
            'sub_title' => $this->sub_title_for_app,
            'question_for' => $this->question_for,
            'selection_type' => match ($this->type_for_app) {
                1 => 'single_choice',
                2 => 'multiple_choice',
                3 => 'text',
                4 => 'slider',
                5 => 'info',
                default => 'unknown',
            },
            'options' => OptionResource::collection($this->whenLoaded('options')),
        ];

        if ($this->relationLoaded('userAnswer') && $this->userAnswer->isNotEmpty()) {
            $answers = $this->userAnswer;
        
            $optionIds = $answers->pluck('option_id')->toArray();
            $response['user_answer'] = [
                'question_id' => $this->id,
                'option_id' => $optionIds,
                'answer' => $answers->pluck('answer')->filter()->first(),
            ];
        
            if ($this->id == 20 && !empty($optionIds)) {
                $suburbDetails = Suburb::whereIn('id', $optionIds)
                    ->get(['id', 'name', 'state', 'postcode']);
        
                $response['user_answer']['suburbs'] = $suburbDetails;
            }
        }

        if ($this->relationLoaded('partnerAnswer') && $this->partnerAnswer->isNotEmpty()) {
            $answers = $this->partnerAnswer;
            $optionIds = $answers->pluck('option_id')->toArray();
            $answerText = $answers->pluck('answer')->filter()->first();
        
            $response['partner_answer'] = [
                'question_id' => $this->id,
                'option_id' => $optionIds,
                'answer' => $answerText,
            ];
        
            if ($this->id == 20 && !empty($optionIds)) {
                $suburbDetails = Suburb::whereIn('id', $optionIds)
                    ->get(['id', 'name', 'state', 'postcode']);
        
                $response['partner_answer']['suburbs'] = $suburbDetails;
            }
        }

        $response['user_answer']['hide_on_profile'] = $this->relationLoaded('privacySetting') && $this->privacySetting
            ? (bool) $this->privacySetting->is_hidden
            : false;
        
        return $response;
    }
}
