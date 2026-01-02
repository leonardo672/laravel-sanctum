<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyCodeRequest extends FormRequest
{

    public function authorize(): bool
    {
      //  return $this->user() !== null;
        return true;
    }

    // VerifyCodeRequest rules
    public function rules()
    {
        return [
            'email' => 'sometimes|required|email|exists:users,email',
            'password' => 'sometimes|required|string',
            'code' => 'required|digits:6'
        ];
    }
}
