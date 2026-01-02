<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateVerificationCodeRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()?->id !== null;
      //  return $this->user() !== null;
      //  return auth()->check(); // Or customize as needed
    }

    public function rules()
    {
        return [
            'user_id' => ['required', 'exists:users,id']
        ];
    }
}
