<?php

namespace App\Exceptions\Registration;

class VerificationLimitExceededException extends VerificationException
{
    protected $code = 429;
    protected $message = 'Too many verification attempts. Please try again later.';
}