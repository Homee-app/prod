<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateCourseRequest extends FormRequest
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
    public function rules()
    {
       
        return [
            /*'course_id' => [
                'required',
                'integer',
                Rule::exists(config('tables.courses'), 'id')->whereNull('deleted_at'),
            ], */            
            'template_id'    => 'required|integer|exists:'.config('tables.templates').',id',
            'template_id'    => 'required|in:1',
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique(config('tables.courses'), 'title')
                ->where(function ($query) {
                    return $query->where('user_id', auth()->id()) // Ensure the title is unique for the current user
                                 ->whereNull('deleted_at') // Ignore soft-deleted records
                                 ->where('id', '!=', $this->route('id')); // Exclude current course
                }),
             ],
            'category_id'    => 'required|integer|exists:'.config('tables.course_categories').',id',
            'description'   => 'required|string',
            'what_you_learn'=> 'required|string',
            'course_fee_type'=> 'required|in:1,2,3', // 1 = Free, 2 = Coins, 3 = Money
            'course_fee'      => 'required_if:course_fee_type,2,3|numeric|min:0',
            //'preview_video' => 'required|url',
            //'preview_image'  => 'required|url',
            'preview_video' => [
                'required',
                'url',
                'regex:/\.(mp4|avi|mov|mkv|heic|hevc|h264)$/i', // Only specific video formats allowed
            ],
            'preview_image' => [
                'required',
                'url',
                'regex:/\.(jpg|jpeg|png|gif|heif)$/i', // Only specific image formats allowed
            ],
            'status'        => 'required|integer|in:1,2,3,4', // 1 = draft', 2='pending', 3 =published, 4=deactivate	
            'tags' => 'nullable|array|max:5', // Ensure max 5 tags
            'tags.*' => 'string|max:50',
        ];
    }

    /**
     * Custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'preview_video.regex' => 'Only MP4, AVI, MOV, HEIC, HEVC, H.264, and MKV formats are allowed for preview videos.',
            'preview_image.regex' => 'Only JPG, JPEG, PNG, HEIF, and GIF formats are allowed for preview images.',
        ];
    }
}
