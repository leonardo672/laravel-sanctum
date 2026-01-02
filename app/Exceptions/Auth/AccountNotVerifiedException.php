<?php
// app/Exceptions/Auth/AccountNotVerifiedException.php

namespace App\Exceptions\Auth;

namespace App\Exceptions\Auth;

class AccountNotVerifiedException extends AuthenticationException
{
    protected $code = 403; // Forbidden
    protected $message = 'Account not verified';

    public function render($request)
    {
        return response()->json([
            'error' => $this->message,
            'code' => $this->code,
            'requires_verification' => true
        ], $this->code);
    }
}