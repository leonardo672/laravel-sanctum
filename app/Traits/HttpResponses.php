<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait HttpResponses
{
    /**
     * Return a JSON success response.
     */
    protected function success(
        mixed $data = null,
        ?string $message = 'Request successful',
        int $code = 200
    ): JsonResponse {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'errors' => null
        ], $code);
    }

    /**
     * Return a JSON error response.
     */
    protected function error(
        ?string $message = 'Something went wrong',
        int $code = 400,
        mixed $errors = null
    ): JsonResponse {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => null,
            'errors' => $errors
        ], $code);
    }

    /**
     * Return a JSON 201 created response.
     */
    protected function created(
        mixed $data = null,
        ?string $message = 'Resource created successfully'
    ): JsonResponse {
        return $this->success($data, $message, 201);
    }

    /**
     * Return a JSON 404 not found response.
     */
    protected function notFound(
        ?string $message = 'Resource not found'
    ): JsonResponse {
        return $this->error($message, 404);
    }

    /**
     * Return a 204 No Content response.
     */
    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }
}
