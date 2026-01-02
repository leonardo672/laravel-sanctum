<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Media;
use Illuminate\Http\UploadedFile;

interface MediaRepositoryInterface
{
    public function updateOrCreateUserImage(User $user, UploadedFile $file): Media;

    public function updateOrCreateUserAudio(User $user, UploadedFile $file): Media;

    public function getUserMediaWithUrl(int $userId): ?Media;

    public function deleteUserMedia(int $userId): void;

    public function getPublicUrl(string $storagePath): string;
}
