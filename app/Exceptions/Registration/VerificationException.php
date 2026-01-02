<?php

namespace App\Exceptions\Registration;

class VerificationException extends RegistrationException
{
    protected $code = 400;
    protected $message = 'Verification process failed';
}