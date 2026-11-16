<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class FileMetadata
{
    /**
     * Resolve the size of a file stored on the public disk.
     */
    public static function size(?string $path): ?int
    {
        if (! is_string($path) || trim($path) === '' || trim($path) === '-') {
            return null;
        }

        try {
            $disk = Storage::disk('public');
            $path = ltrim(trim($path), '/');

            return $disk->exists($path) ? $disk->size($path) : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
