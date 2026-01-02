<?php

namespace App\Exceptions\Registration;

class VerificationCodeExpiredException extends VerificationException
{
    protected $code = 410;
    protected $message = 'The verification code has expired';
}