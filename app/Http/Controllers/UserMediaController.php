<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserMediaUploadRequest;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\MediaRepositoryInterface;
use App\Traits\HandlesMediaMimeTypes;
use App\Traits\HandlesMediaResponses;
use Illuminate\Http\JsonResponse;

class UserMediaController extends Controller
{
    use HandlesMediaMimeTypes, HandlesMediaResponses;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private MediaRepositoryInterface $mediaRepository
    ) {}

    /**
     * Upload or update user's media (image/audio)
     */
    public function store(UserMediaUploadRequest $request, int $userId): JsonResponse
    {
        $file = $request->file('media');
        $user = $this->userRepository->findOrFail($userId);

        if ($this->isImage($file)) {
            $media = $this->mediaRepository->updateOrCreateUserImage($user, $file);
        } elseif ($this->isAudio($file)) {
            $media = $this->mediaRepository->updateOrCreateUserAudio($user, $file);
        } else {
            return response()->json([
                'message' => 'Unsupported media type. Please upload a valid image or audio file.'
            ], 422);
        }

        return $this->mediaUploadSuccessResponse($media);
    }

    /**
     * Show user's media
     */
    public function show(int $userId): JsonResponse
    {
        $media = $this->mediaRepository->getUserMediaWithUrl($userId);

        if (!$media) {
            return $this->mediaNotFoundResponse();
        }

        return response()->json(['data' => $media]);
    }

    /**
     * Delete user's media
     */
    public function destroy(int $userId): JsonResponse
    {
        $this->mediaRepository->deleteUserMedia($userId);

        return $this->mediaDeleteSuccessResponse();
    }
}
