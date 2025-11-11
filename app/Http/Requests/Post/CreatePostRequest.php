<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

class CreatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Change this if you need authentication logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'caption' => 'required|string|max:500',
            'thumbnail' => 'nullable|url', // Must be null or a valid URL
            'attachments' => 'required|array|max:5',
            'attachments.*' => 'url', // Each attachment must be a valid URL
            'tags' => 'required|array|max:5',
            'tags.*' => 'string|max:50',
        ];
    }
}
