<?php
// app/Exceptions/Auth/TokenGenerationException.php

namespace App\Exceptions\Auth;

class TokenGenerationException extends AuthenticationException
{
    protected $code = 500;
    protected $message = 'Failed to generate authentication token';

    public function __construct()
    {
        parent::__construct($this->message, $this->code);
    }
}