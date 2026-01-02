<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Toggle2faRequest extends FormRequest
{
    public function authorize()
    {
        
        return $this->user()?->id !== null;
      //  return $this->user() !== null;
       // return auth()->check(); // User must be authenticated
    }

    public function rules()
    {
        return []; // No specific inputs yet, but ready for expansion
    }
}
