<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use finfo;

trait HandlesMediaMimeTypes
{
    private array $allowedImageTypes = [
        'image/jpeg',
        'image/png',
        'image/jpg',
        'image/gif',
    ];

    private array $allowedAudioTypes = [
        'audio/mpeg',
        'audio/mp3',
        'audio/wav',
    ];

    public function isImage(UploadedFile $file): bool
    {
        $mimeType = $this->detectMimeType($file);
        return in_array($mimeType, $this->allowedImageTypes);
    }

    public function isAudio(UploadedFile $file): bool
    {
        $mimeType = $this->detectMimeType($file);
        return in_array($mimeType, $this->allowedAudioTypes);
    }

    public function detectMimeType(UploadedFile $file): string
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return $finfo->file($file->getRealPath());
    }
}
