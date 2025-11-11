<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Update with proper authorization logic if needed.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'post_id'     => ['required', 'integer', "exists:".config('tables.posts').",id"],
            'caption'     => ['required', 'string', 'max:255'],
            'thumbnail' => 'nullable|url', // Must be null or a valid URL
            'attachments' => 'required|array|max:5',
            'attachments.*' => 'url', // Each attachment must be a valid URL
            'tags' => 'required|array|max:5',
            'tags.*'      => ['string', 'max:50'], // each tag must be a string with a max length of 50 characters
        ];
    }
}
