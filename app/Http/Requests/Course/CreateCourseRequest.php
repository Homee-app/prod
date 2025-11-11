<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCourseRequest extends FormRequest
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
        $courseId = $this->input('course_id'); // Get course_id from request
        return [
            'course_id' => 'nullable|integer|exists:' . config('tables.courses') . ',id',
             'template_id'    => 'required|integer|exists:'.config('tables.templates').',id',
             'template_id'    => 'required|in:1',
             //'title'          => 'required|string|max:255',
             'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique(config('tables.courses'), 'title')
                    ->ignore($courseId) // Ignore the course being updated
                    ->where(function ($query) {
                        return $query->where('user_id', auth()->id())
                                    ->whereNull('deleted_at');
                    }),
              ],
             'category_id'    => 'required|integer|exists:'.config('tables.course_categories').',id',
             'description'    => 'required|string',
             'what_you_learn' => 'required|string',
             'course_fee_type'=> 'required|in:1,2,3', // 1 = Free, 2 = Coins, 3 = Money
             'course_fee'      => 'required_if:course_fee_type,2,3|numeric|min:0',
             //'preview_video'  => 'required|url',
             //'preview_image'  => 'required|url',
            'preview_video' => [
                'required',
                'url',
                'regex:/\.(mp4|avi|mov|mkv|heic|hevc|h264|h.264)$/i', // Only specific video formats allowed
            ],
            'preview_image' => [
                'required',
                'url',
                'regex:/\.(jpg|jpeg|png|gif|heif)$/i', // Only specific image formats allowed
            ],
             'tags' => 'nullable|array|max:5', // Ensure max 5 tags
             'tags.*' => 'string|max:50',
             'status'         => 'nullable|in:1,2,3', // 1 = draft', 2='pending', 3 =published, 4=deactivate	
         ];
     }

     /**
     * Custom error messages for validation rules.
     */
    public function oldmessages(): array
    {
        return [
            'preview_video.regex' => 'Only MP4, AVI, MOV, HEIC, HEVC, H.264, and MKV formats are allowed for course videos.',
            'preview_image.regex' => 'Only JPG, JPEG, PNG, HEIF, and GIF formats are allowed for course images.',
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.integer' => 'Invalid course ID format.',
            'course_id.exists' => 'The selected course does not exist.',

            'template_id.required' => 'Template is required.',
            'template_id.integer' => 'Invalid template ID format.',
            'template_id.exists' => 'The selected template does not exist.',
            'template_id.in' => 'Only template ID 1 is allowed.',

            'title.required' => 'Course title is required.',
            'title.string' => 'Course title must be a string.',
            'title.max' => 'Course title must not exceed 255 characters.',
            'title.unique' => 'You already have a course with this title.',

            'category_id.required' => 'Please select a course category.',
            'category_id.integer' => 'Invalid category ID format.',
            'category_id.exists' => 'The selected category does not exist.',

            'description.required' => 'Course description is required.',
            'description.string' => 'Course description must be a string.',

            'what_you_learn.required' => 'The "what you’ll learn" section is required.',
            'what_you_learn.string' => 'The "what you’ll learn" section must be a string.',

            'course_fee_type.required' => 'Course fee type is required.',
            'course_fee_type.in' => 'Course fee type must be one of: Free, Coins, or Money.',

            'course_fee.required_if' => 'Course fee is required when the course is not free.',
            'course_fee.numeric' => 'Course fee must be a numeric value.',
            'course_fee.min' => 'Course fee must be at least 0.',

            'preview_video.required' => 'Course video is required.',
            'preview_video.url' => 'Course video must be a valid URL.',
            'preview_video.regex' => 'Only MP4, AVI, MOV, MKV, HEIC, HEVC, H.264 formats are allowed for the video.',

            'preview_image.required' => 'Course image is required.',
            'preview_image.url' => 'Course image must be a valid URL.',
            'preview_image.regex' => 'Only JPG, JPEG, PNG, GIF, HEIF formats are allowed for the image.',

            'tags.array' => 'Tags must be an array.',
            'tags.max' => 'You can assign up to 5 tags only.',
            'tags.*.string' => 'Each tag must be a string.',
            'tags.*.max' => 'Each tag must not exceed 50 characters.',

            'status.in' => 'Status must be either draft, pending, or published.',
        ];
    }


}
