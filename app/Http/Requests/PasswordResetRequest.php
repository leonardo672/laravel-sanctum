<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Traits\HttpResponses;

class PasswordResetRequest extends FormRequest
{
    use HttpResponses;
    
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Password reset should be available to all users (even unauthenticated)
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
        ];
    }

    /**
     * Custom error messages for validation.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required',
            'email.email' => 'Please enter a valid email address',
            'email.max' => 'Email must not exceed 255 characters',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        // Use the error response format from HttpResponses trait
        $response = $this->error(
            $validator->errors()->toArray(),
            'Validation failed',
            422
        );

        // Throw a HttpResponseException with the formatted response
        throw new \Illuminate\Http\Exceptions\HttpResponseException($response);
    }

    /**
     * Prepare the data for validation (convert email to lowercase).
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower($this->email),
        ]);
    }
}