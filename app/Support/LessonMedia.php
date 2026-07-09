<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class LessonMedia
{
    public static function store(UploadedFile $file, string $contentType): string
    {
        $directory = match ($contentType) {
            'video' => 'lessons/videos',
            'image' => 'lessons/images',
            'pdf' => 'lessons/pdfs',
            default => 'lessons/files',
        };

        $path = $file->store($directory, 'public');

        return 'storage/'.$path;
    }

    public static function delete(?string $mediaPath): void
    {
        if (! $mediaPath || ! str_starts_with($mediaPath, 'storage/')) {
            return;
        }

        Storage::disk('public')->delete(substr($mediaPath, strlen('storage/')));
    }
}
