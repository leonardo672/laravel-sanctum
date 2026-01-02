<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait HandlesMediaResponses
{
    protected function mediaUploadSuccessResponse($media, string $message = 'Media uploaded successfully', int $statusCode = 201): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $media,
            'url' => $this->mediaRepository->getPublicUrl($media->url),
        ], $statusCode);
    }

    protected function mediaNotFoundResponse(string $message = 'No media found for user', int $statusCode = 404): JsonResponse
    {
        return response()->json(['message' => $message], $statusCode);
    }

    protected function mediaDeleteSuccessResponse(string $message = 'Media deleted successfully'): JsonResponse
    {
        return response()->json(['message' => $message]);
    }
}
