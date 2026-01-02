<?php
// app/Exceptions/Auth/InvalidCredentialsException.php

namespace App\Exceptions\Auth;

use Exception;

namespace App\Exceptions\Auth;

class InvalidCredentialsException extends AuthenticationException
{
    protected $code = 401;
    protected $message = 'Invalid email or password';

    public function __construct()
    {
        parent::__construct($this->message, $this->code);
    }
}