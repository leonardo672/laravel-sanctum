<?php
// app/Exceptions/Auth/VerificationException.php

namespace App\Exceptions\Auth;

class VerificationException extends AuthenticationException
{
    protected $code = 500;
    protected $message = 'Failed to process verification request';

    public function __construct()
    {
        parent::__construct($this->message, $this->code);
    }
}