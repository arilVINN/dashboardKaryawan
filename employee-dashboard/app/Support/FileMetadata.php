<?php

namespace App\Support;

class FileMetadata
{
    /**
     * Resolve the size of a stored file, checking the private disk first and
     * falling back to the legacy public disk.
     */
    public static function size(?string $path): ?int
    {
        return FileStorage::size($path);
    }
}
