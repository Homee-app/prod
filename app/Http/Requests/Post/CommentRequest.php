<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommentRequest extends FormRequest
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
        if ($this->isMethod('post')) {
            return [
                //'user_id'   => 'required|exists:'.config('tables.users').',id',
                'comment'   => 'required|string',
                'parent_id' => [
                    'nullable',
                    Rule::exists(config('tables.post_comments'), 'id')->where(function ($query) {
                        // Ensure the parent comment is not itself nested
                        $query->whereNull('parent_id');
                    })
                ],
            ];
        } elseif ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'comment' => 'required|string',
            ];
        }
        
        return [];
    }
    
}
