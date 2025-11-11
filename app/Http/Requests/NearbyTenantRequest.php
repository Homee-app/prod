<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class NearbyTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorize all users for now
    }

    public function rules(): array
    {
        return [
            'suburb_ids' => 'nullable|array',            
            //'gender' => 'nullable|integer',
            'gender_option_ids' => 'nullable|array',
            'gender_option_ids.*' => 'integer|exists:question_options,id',
            'suburb_ids.*' => 'integer|exists:suburbs,id',
            'min_budget' => 'nullable|numeric|min:0',
            'max_budget' => 'nullable|numeric|min:0',
            //'availability_date' => ['nullable', 'date_format:Y-m-d'],
            'availability_date' => ['nullable', 'date_format:d/m/Y'],
            'stay_length_option_id' => 'nullable|integer|exists:question_options,id',
            'employment_status_option_ids' => 'nullable|array',
            'employment_status_option_ids.*' => 'integer|exists:question_options,id',
            'dietary_option_ids' => 'nullable|array',
            'dietary_option_ids.*' => 'integer|exists:question_options,id',
            'interest_option_ids' => 'nullable|array',
            'interest_option_ids.*' => 'integer|exists:question_options,id',
            'sexuality_option_ids' => 'nullable|array',
            'sexuality_option_ids.*' => 'integer|exists:question_options,id',
            'religion_option_ids' => 'nullable|array',
            'religion_option_ids.*' => 'integer|exists:question_options,id',
            'ethnicity_option_ids' => 'nullable|array',
            'ethnicity_option_ids.*' => 'integer|exists:question_options,id',
            'min_age' => 'nullable|integer|min:1|max:120',
            'max_age' => 'nullable|integer|min:1|max:120',
            'language_option_ids' => 'nullable|array',
            'language_option_ids.*' => 'integer|exists:question_options,id',
            'political_view_option_ids' => 'nullable|array',
            'political_view_option_ids.*' => 'integer|exists:question_options,id',
            'open_to_guests' => 'nullable|boolean',
            'has_rental_history' => 'nullable|boolean',
            'prefers_non_drinker' => 'nullable|boolean',
            'prefers_non_smoker' => 'nullable|boolean',
            'min_lifestyle_match_percent' => 'nullable|integer|min:0|max:100',
            'max_distance' => 'nullable|numeric|min:1',
            'show_verified_only' => 'nullable|boolean',
            'open_to_pets' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $firstError = $validator->errors()->first();

        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $firstError,
        ], 422));
    }
}
