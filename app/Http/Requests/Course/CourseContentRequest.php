<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class CourseContentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true; // Change this based on authentication/authorization rules
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */      

     public function rules()
    {
        return [
            'course_id' => [
                'required',
                'integer',
                Rule::exists(config('tables.courses'), 'id')->whereNull('deleted_at'),
            ],
            'course_contents' => 'required|array',
            
             // Common validations for all content types
             'course_contents.*.course_content_id' => [
                'nullable',
                'integer',
                Rule::exists(config('tables.course_content'), 'id')->whereNull('deleted_at'),
            ],
            'course_contents.*.title' => 'required|string|max:255',
            'course_contents.*.content_type' => 'required|in:text,image,video,quiz,quiz_sub',
            'course_contents.*.course_content_id' => 'nullable|integer|exists:'.config('tables.course_content').',id',
            'course_contents.*.content' => 'nullable|string',

            // General validation for image and video arrays
            'course_contents.*.img' => 'nullable|array|max:5',
            'course_contents.*.img.*' => 'url',

            'course_contents.*.video' => 'nullable|array|max:3',
            'course_contents.*.video.*' => 'url',

            'course_contents.*.quiz' => 'nullable|array',
            'course_contents.*.quiz.*.question' => 'required_with:course_contents.*.quiz|string',
            'course_contents.*.quiz.*.type' => 'required_with:course_contents.*.quiz|string|in:objective,subjective',
            'course_contents.*.quiz.*.options' => 'nullable|array',
            //'course_contents.*.quiz.*.answer' => 'required_with:course_contents.*.quiz|string',
            'course_contents.*.quiz.*.answer' => 'nullable:course_contents.*.quiz|string',

            'course_contents.*.is_required' => 'boolean',
            
            //'course_contents.*.order' => 'required|integer',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $courseContents = $this->input('course_contents', []);

            foreach ($courseContents as $index => $content) {
                // Validation when content_type = image
                if ($content['content_type'] === 'image') {
                    if (empty($content['img']) || !is_array($content['img'])) {
                        $validator->errors()->add("course_contents.$index.img", "Image is required when content type is 'image'.");
                    } else {
                        foreach ($content['img'] as $key => $imgUrl) {
                            if (!filter_var($imgUrl, FILTER_VALIDATE_URL)) {
                                $validator->errors()->add("course_contents.$index.img.$key", "Each image must be a valid URL.");
                            }
                            if (!preg_match('/\.(jpg|jpeg|png|gif|HEIF)$/i', $imgUrl)) {
                                $validator->errors()->add("course_contents.$index.img.$key", "Only jpg, jpeg, png,HEIF and gif formats are allowed.");
                            }
                        }
                    }
                }

                // Validation when content_type = video
                if ($content['content_type'] === 'video') {
                    if (empty($content['video']) || !is_array($content['video'])) {
                        $validator->errors()->add("course_contents.$index.video", "Video is required when content type is 'video'.");
                    } else {
                        foreach ($content['video'] as $key => $videoUrl) {
                            if (!filter_var($videoUrl, FILTER_VALIDATE_URL)) {
                                $validator->errors()->add("course_contents.$index.video.$key", "Each video must be a valid URL.");
                            }
                            if (!preg_match('/\.(mp4|avi|mov|mkv|HEIC|HEVC|H.264)$/i', $videoUrl)) {
                                $validator->errors()->add("course_contents.$index.video.$key", "Only mp4, avi, mov,HEIC, HEVC, H.264 and mkv formats are allowed.");
                            }
                        }
                    }
                }

                // Ensure answer is in options if type is subjective
                if (isset($content['quiz']) && is_array($content['quiz'])) {
                    foreach ($content['quiz'] as $qIndex => $quiz) {
                        if ($quiz['type'] === 'objective') {
                            if (!isset($quiz['options']) || !is_array($quiz['options'])) {
                                $validator->errors()->add("course_contents.$index.quiz.$qIndex.options", "Options are required for objective quizzes.");
                            } else {
                                $optionsCount = count($quiz['options']);
                                if ($optionsCount < 2 || $optionsCount > 4) {
                                    $validator->errors()->add("course_contents.$index.quiz.$qIndex.options", "Objective quizzes must have between 2 and 4 options.");
                                }
                                if (!in_array($quiz['answer'], $quiz['options'])) {
                                    $validator->errors()->add("course_contents.$index.quiz.$qIndex.answer", "The answer must be one of the options for objective quizzes.");
                                }
                            }
                        }
                    }
                }

                if (in_array($content['content_type'], ['text', 'image', 'video'])) {
                    if (empty($content['content'])) {
                        $validator->errors()->add("course_contents.$index.content", "The content field is required when content type is '{$content['content_type']}'.");
                    }
                }
            }
        });
    }

    
    public function messages(): array
    {
        return [
            'course_id.required' => 'Course ID is required.',
            'course_id.integer' => 'Course ID must be an integer.',
            'course_id.exists' => 'Course not found.',

            'course_contents.required' => 'Course contents are required.',
            'course_contents.array' => 'Course contents must be an array.',

            'course_contents.*.course_content_id.integer' => 'Course content ID must be an integer.',
            'course_contents.*.course_content_id.exists' => 'Course content not found.',

            'course_contents.*.title.required' => 'Each course content must have a title.',
            'course_contents.*.title.string' => 'Content title must be a string.',
            'course_contents.*.title.max' => 'Content title cannot exceed 255 characters.',

            'course_contents.*.content_type.required' => 'Content type is required.',
            'course_contents.*.content_type.in' => 'Invalid content type provided.',

            'course_contents.*.content.string' => 'Content must be a string.',

            'course_contents.*.img.array' => 'Images must be an array.',
            'course_contents.*.img.*.url' => 'Each image must be a valid URL.',
            'course_contents.*.img.max' => 'Each course content can have a maximum of 5 images.',

            'course_contents.*.video.array' => 'Videos must be an array.',
            'course_contents.*.video.*.url' => 'Each video must be a valid URL.',
            'course_contents.*.video.max' => 'Each course content can have a maximum of 3 videos.',

            'course_contents.*.quiz.array' => 'Quiz must be an array.',
            'course_contents.*.quiz.*.question.required_with' => 'Quiz question is required.',
            'course_contents.*.quiz.*.type.required_with' => 'Quiz type is required.',
            'course_contents.*.quiz.*.type.in' => 'Quiz type must be either objective or subjective.',
            'course_contents.*.quiz.*.options.array' => 'Quiz options must be an array.',
            'course_contents.*.quiz.*.answer.required_with' => 'Answer is required for the quiz.',
            'course_contents.*.quiz.*.answer.string' => 'Quiz answer must be a string.',

            'course_contents.*.is_required.boolean' => 'The required field must be true or false.',
        ];
    }

     
}
