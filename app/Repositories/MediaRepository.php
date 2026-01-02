<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use finfo;

class MediaRepository implements MediaRepositoryInterface
{
    private const PROFILE_IMAGE_DISK = 'public';
    private const PROFILE_IMAGE_DIR = 'users';
    private const AUDIO_DIR = 'audios';

    private const ALLOWED_IMAGE_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/jpg',
        'image/gif',
    ];

    private const ALLOWED_AUDIO_MIME_TYPES = [
        'audio/mpeg',
        'audio/mp3',
        'audio/wav',
    ];

    public function updateOrCreateUserImage(User $user, UploadedFile $file): Media
    {
        $this->validateImageFile($file);

        $filename = $this->generateUniqueFilename($file);
        $path = $file->storeAs(self::PROFILE_IMAGE_DIR, $filename, self::PROFILE_IMAGE_DISK);
        $publicPath = str_replace('public/', '', $path);

        // Delete old image if exists
        if ($user->media) {
            $this->deleteMediaFile($user->media);
        }

        return $user->media()->updateOrCreate(
            ['type' => 'profile_image'],
            ['url' => $publicPath]
        );
    }

    public function updateOrCreateUserAudio(User $user, UploadedFile $file): Media
    {
        $this->validateAudioFile($file);

        $filename = $this->generateUniqueFilename($file);
        $path = $file->storeAs(self::AUDIO_DIR, $filename, self::PROFILE_IMAGE_DISK);
        $publicPath = str_replace('public/', '', $path);

        // Delete old audio if exists
        if ($user->media) {
            $this->deleteMediaFile($user->media);
        }

        return $user->media()->updateOrCreate(
            ['type' => 'audio'],
            ['url' => $publicPath]
        );
    }

    public function getUserMediaWithUrl(int $userId): ?Media
    {
        $media = Media::where('mediable_id', $userId)
            ->where('mediable_type', User::class)
            ->first();

        if ($media) {
            $media->public_url = $this->getPublicUrl($media->url);
        }

        return $media;
    }

    public function deleteUserMedia(int $userId): void
    {
        $media = Media::where('mediable_id', $userId)
            ->where('mediable_type', User::class)
            ->first();

        if ($media) {
            $this->deleteMediaFile($media);
            $media->delete();
        }
    }

    public function getPublicUrl(string $storagePath): string
    {
        return asset('storage/' . ltrim($storagePath, '/'));
    }

    private function validateImageFile(UploadedFile $file): void
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file->getRealPath());

        if (!in_array($mimeType, self::ALLOWED_IMAGE_MIME_TYPES)) {
            throw new \InvalidArgumentException('Invalid image content type');
        }
    }

    private function validateAudioFile(UploadedFile $file): void
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file->getRealPath());

        if (!in_array($mimeType, self::ALLOWED_AUDIO_MIME_TYPES)) {
            throw new \InvalidArgumentException('Invalid audio content type');
        }
    }

    private function generateUniqueFilename(UploadedFile $file): string
    {
        return uniqid() . '.' . $file->getClientOriginalExtension();
    }

    private function deleteMediaFile(Media $media): void
    {
        Storage::disk(self::PROFILE_IMAGE_DISK)->delete($media->url);
    }
}
