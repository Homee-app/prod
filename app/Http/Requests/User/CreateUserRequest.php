<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException; // Import for API responses
use Illuminate\Validation\Rule;
class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Allow all requests (customize as needed later)
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $usersTable = config('tables.users');
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            //'email' => 'required|email|unique:zl_users', // Add unique rule for email
            'email' => [
                'required',
                'email',
                Rule::unique($usersTable)->whereNull('deleted_at') // Exclude soft-deleted users
            ],
            'password' => 'required|min:6|confirmed', // Add confirmed rule for password
            'dob' => 'required|date_format:Y-m-d',
            'profile_photo' => 'nullable', // Validate image upload
           // 'country_id' => 'required|integer', // Validate country
            //'proof_of_age' => 'required|boolean', // Or 'required|string' if it's not a boolean
            'device_type' => 'required|in:android,ios', // Ensure device type is either 'android' or 'ios'
            'device_id' => 'required', // Ensure device ID is provided for tracking the device
            'role' => 'required|in:child,parent,business', // Use string values for roles
        ];

        
        $role = $this->input('role');
        $dob = $this->input('dob');
        $dob = new \DateTime($dob);
        $currentDate = new \DateTime();        
        $ageInterval = $dob->diff($currentDate);
        $age = $ageInterval->y;
       
 
        // Role-specific rules
       switch ($role) {
        case 'child':              
           // $rules['parent_phone'] = 'nullable|digits_between:7,15'; // Example parent-specific field
           // Check if the child is above 16, if so, do not require parent_email
           if ($age < 16) {
                $rules['parent_email'] = 'required|email'; // Parent email is required for children under or equal to 16
            } 
           //$rules['parent_email'] = 'required|email'; // Example parent-specific field
            break;
            case 'parent':
              //  $rules['phone_no'] = 'nullable|required_if:role,parent|digits_between:7,15'; // Example parent-specific field
                break;
            case 'business':
                $rules['business_field'] = 'nullable|string|max:200'; // Example business-specific field
                break;
        } 

        return $rules;
    }

    /**
     * Prepare the data for validation. This method will run before validation.
     * If the role is child, we will set the role_id to 1.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Check if the role is 'child' and set the role_id accordingly
        if ($this->input('role') === 'child') {
            // Add the role_id field to the input data
            $this->merge([
                'role_id' => 1,
            ]);
        }
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'email' => 'email address',
            'password' => 'password',
            'dob' => 'date of birth',
            'profile_photo' => 'profile photo',
            'proof_of_age' => 'proof of age',
            //'parent_phone' => 'parent phone',
            'parent_email' => 'parent email',
            'parent_field' => 'parent field',
            'business_field' => 'business field',
        ];
    }

    /**
     * Handle a failed validation attempt. For API requests.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422)); // 422 Unprocessable Entity for API validation errors
    }
}
