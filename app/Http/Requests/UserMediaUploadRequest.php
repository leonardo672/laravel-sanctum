<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserMediaUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'media' => 'required|file|max:10240', // 10MB max, general rule
        ];
    }

    public function messages(): array
    {
        return [
            'media.required' => 'A file must be provided.',
            'media.max' => 'The file must not be larger than 10MB.',
        ];
    }
}
