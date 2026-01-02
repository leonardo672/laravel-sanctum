<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Traits\HttpResponses;
use Illuminate\Validation\Rules\Password;
use Illuminate\Http\Exceptions\HttpResponseException;

class PasswordUpdateRequest extends FormRequest
{
    use HttpResponses;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is handled by the token validation in controller
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ];
    }

    /**
     * Custom validation error messages.
     */
    public function messages(): array
    {
        return [
            'token.required' => 'Reset token is required',
            'password.required' => 'New password is required',
            'password.confirmed' => 'Password confirmation does not match',
            'password.min' => 'Password must be at least 8 characters',
        ];
    }

    /**
     * Handle failed validation.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Password validation failed',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}