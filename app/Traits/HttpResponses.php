<?php

namespace App\Traits;

trait HttpResponses {
    /**
     * Successful HTTP response
     */
    protected function success(
        mixed $data = null,
        ?string $message = null,
        int $code = 200
    ) {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }
    
    /**
     * Error HTTP response
     */
    protected function error(
        ?string $message = null,
        int $code = 400,
        mixed $data = null
    ) {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Resource created response (201)
     */
    protected function created(
        mixed $data = null,
        ?string $message = 'Resource created successfully'
    ) {
        return $this->success($data, $message, 201);
    }

    /**
     * Not found response (404)
     */
    protected function notFound(
        ?string $message = 'Resource not found'
    ) {
        return $this->error($message, 404);
    }
}