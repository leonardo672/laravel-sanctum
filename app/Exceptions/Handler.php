<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use App\Exceptions\Auth\AuthenticationException;
use App\Exceptions\Registration\RegistrationException;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Log or report if needed
        });
    }

    public function render($request, Throwable $exception)
    {
        // Handle all custom auth exceptions
        if ($exception instanceof AuthenticationException) {
            return $exception->render($request);
        }

        // Handle all custom registration exceptions
        if ($exception instanceof RegistrationException) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
                'code' => $exception->getCode()
            ], $exception->getCode() ?: 400);
        }

        return parent::render($request, $exception);
    }
}
