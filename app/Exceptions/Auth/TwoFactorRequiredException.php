<?php
// app/Exceptions/Auth/TwoFactorRequiredException.php

namespace App\Exceptions\Auth;

class TwoFactorRequiredException extends AuthenticationException
{
    protected $code = 423; // (status code for "Locked," indicating a resource is temporarily unavailable, like waiting for 2FA
    protected $message = 'Two-factor authentication required';  
    protected $responseData;

    public function __construct(array $responseData)
    {
        parent::__construct($this->message, $this->code); // This calls the constructor of the parent class (AuthenticationException) to initialize the exception with:message, code

        $this->responseData = $responseData;
    }

    public function getData(): array
    {
        return $this->responseData;
    }
}