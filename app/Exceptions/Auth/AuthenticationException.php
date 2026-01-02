<?php
// app/Exceptions/Auth/AuthenticationException.php

namespace App\Exceptions\Auth;

use Exception;

abstract class AuthenticationException extends Exception // base exception for authentication-related errors
{
    protected $code = 400; // Bad Request
    protected $message = 'Authentication error';

    public function render($request)
    {
        return response()->json([
            'error' => $this->message,
            'code' => $this->code,
            'type' => class_basename($this)
        ], $this->code);
    }
}