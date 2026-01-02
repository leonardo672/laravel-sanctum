<?php

namespace App\Exceptions\Auth;

class InvalidRefreshTokenException extends AuthenticationException
{
    protected $code = 401;
    protected $message = 'Invalid or expired refresh token';

    public function render($request)
    {
        return response()->json([
            'status' => 'error',
            'message' => $this->message,
            'data' => null
        ], $this->code);
    }
}