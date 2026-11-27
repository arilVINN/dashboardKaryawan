<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Single place that knows where sensitive uploaded files live.
 *
 * New uploads are written to the private disk (storage/app/private), which is
 * not reachable through the /storage symlink. Legacy files may still exist on
 * the public disk until `php artisan storage:privatize` has been run, so reads
 * transparently fall back to the public disk. Writes/deletes always target the
 * private disk first.
 */
final class FileStorage
{
    public const DISK = 'private';

    public const LEGACY_DISK = 'public';

    /**
     * Stored-path prefixes that must never be publicly reachable.
     * Everything under these prefixes is served by SecureFileController after
     * an authorization check.
     */
    public const SENSITIVE_PREFIXES = [
        'keuangan',
        'pesan-lampiran',
        'lampiran_pesan',
        'tugas_pendukung',
        'submissions',
        'revisi',
        'surat_izin',
        'tanda_tangan',
        'tanda_tangan_surat_izin',
        'daily-journals',
        'tower_files',
    ];

    public static function normalise(?string $path): ?string
    {
        if (! is_string($path)) {
            return null;
        }

        $path = ltrim(trim($path), '/');

        if ($path === '' || $path === '-' || str_contains($path, '..') || str_contains($path, "\0")) {
            return null;
        }

        return $path;
    }

    public static function isSensitive(?string $path): bool
    {
        $path = self::normalise($path);

        if ($path === null) {
            return false;
        }

        return in_array(explode('/', $path, 2)[0], self::SENSITIVE_PREFIXES, true);
    }

    /**
     * Return the disk that currently holds the file: private first, then the
     * legacy public disk. Null when the file cannot be found.
     */
    public static function diskFor(?string $path): ?string
    {
        $path = self::normalise($path);

        if ($path === null) {
            return null;
        }

        foreach ([self::DISK, self::LEGACY_DISK] as $disk) {
            try {
                if (Storage::disk($disk)->exists($path)) {
                    return $disk;
                }
            } catch (\Throwable) {
                // Ignore unreadable/misconfigured disks and try the next one.
            }
        }

        return null;
    }

    public static function exists(?string $path): bool
    {
        return self::diskFor($path) !== null;
    }

    public static function size(?string $path): ?int
    {
        $path = self::normalise($path);

        if ($path === null) {
            return null;
        }

        $disk = self::diskFor($path);

        if ($disk === null) {
            return null;
        }

        try {
            return Storage::disk($disk)->size($path);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Remove a file from both the private and legacy public disks.
     */
    public static function delete(?string $path): void
    {
        $path = self::normalise($path);

        if ($path === null) {
            return;
        }

        foreach ([self::DISK, self::LEGACY_DISK] as $disk) {
            try {
                Storage::disk($disk)->delete($path);
            } catch (\Throwable) {
                // Best-effort cleanup.
            }
        }
    }

    /**
     * URL for a sensitive file. Sensitive paths always go through the
     * authorized /files route; everything else keeps the public /storage URL.
     */
    public static function url(?string $path): ?string
    {
        $path = self::normalise($path);

        if ($path === null) {
            return null;
        }

        return self::isSensitive($path)
            ? url('/files/'.$path)
            : url('/storage/'.$path);
    }
}
