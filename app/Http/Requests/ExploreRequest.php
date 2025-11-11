<?php

namespace App\Http\Requests;

use App\Models\QuestionsOption;
use App\Models\Suburb;
use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;

class ExploreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'radius' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'suburb_ids' => 'nullable|array',
            'suburb_ids.*' => 'numeric|exists:' . Suburb::class . ',id',
            'property_id' => 'nullable|exists:' . Property::class . ',id',
            'sortBy' => 'nullable|in:0,1,2,3,4|numeric',
            'location' => 'nullable|string',
            'min_rent' => 'nullable|numeric',
            'max_rent' => 'nullable|numeric',
            'bills_included' => 'nullable|numeric',
            'availability' => 'nullable|date_format:d/m/Y',
            'min_length_of_stay' => 'nullable|string',
            'max_length_of_stay' => 'nullable|string',
            'flexible' => 'boolean',
            'housemate_preferences' => 'nullable|numeric',
            'accommodation' => 'nullable|array',
            'accommodation.*' => 'numeric|exists:' . QuestionsOption::class . ',id',

            'places_accepting' => 'nullable|array',
            'places_accepting.*' => 'numeric',

            'home_accessibility' => 'nullable|array',
            'home_accessibility.*' => 'numeric',

            'furnishings' => 'nullable|numeric',
            'bathroom_type' => 'nullable|numeric',
            'number_of_housemates_occupied' => 'nullable|numeric',
            'parking_type' => 'nullable|numeric',

            'property_facilities' => 'nullable|numeric',
            'property_facilities.*' => 'numeric|exists:' . QuestionsOption::class . ',id',

            'subscriber_filters' => 'nullable|array',
            'subscriber_filters.*.type' => 'nullable|string',
            'subscriber_filters.*.min' => 'nullable|numeric',
            'subscriber_filters.*.max' => 'nullable|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'sortBy.in' => 'The sortBy value must be one of: 0, 1, 2, 3, or 4.',
            'sortBy.numeric' => 'The sortBy value must be a number.',

            'location.string' => 'The location must be a string.',

            'min_rent.numeric' => 'The minimum rent must be a number.',
            'max_rent.numeric' => 'The maximum rent must be a number.',

            'bills_included.numeric' => 'Bills included must be a number.',

            'availability.date_format' => 'The availability date must be in the format DD/MM/YYYY.',

            'min_length_of_stay.numeric' => 'The minimum length of stay must be a number.',
            'max_length_of_stay.numeric' => 'The maximum length of stay must be a number.',

            'accommodation.numeric' => 'Accommodation must be a number.',
            'places_accepting.array' => 'Places accepting must be an array.',
            'places_accepting.*.numeric' => 'Each place accepting value must be a number.',
            'home_accessibility.array' => 'Home accessibility must be an array.',
            'home_accessibility.*.numeric' => 'Each home accessibility value must be a number.',
            'furnishings.numeric' => 'Furnishings must be a number.',
            'bathroom_type.numeric' => 'Bathroom type must be a number.',
            'number_of_housemates_occupied.numeric' => 'Number of housemates occupied must be a number.',
            'parking_type.numeric' => 'Parking type must be a number.',

            'property_facilities.numeric' => 'Property facilities must be a number.',
            'property_facilities.*.numeric' => 'Each property facility value must be a number.',

            'subscriber_filters.array' => 'Subscriber filters must be an array.',
            'subscriber_filters.*.type.string' => 'Subscriber filter type must be a string.',
            'subscriber_filters.*.min.numeric' => 'Subscriber filter minimum value must be a number.',
            'subscriber_filters.*.max.numeric' => 'Subscriber filter maximum value must be a number.',
        ];
    }
}
