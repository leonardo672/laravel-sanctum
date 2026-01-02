<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Allow only authenticated users to comment
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'body' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'Comment body is required.',
            'body.max' => 'Comment must not exceed 1000 characters.',
            'parent_id.exists' => 'The reply must refer to a valid comment.',
        ];
    }
}
