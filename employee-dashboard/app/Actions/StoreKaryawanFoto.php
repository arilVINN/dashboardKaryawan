<?php

namespace App\Actions;

use App\Models\Karyawan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class StoreKaryawanFoto
{
    /**
     * Replace the karyawan's profile photo on the PRIVATE disk, deleting the
     * previous file. Returns the stored relative path.
     */
    public static function handle(Karyawan $karyawan, UploadedFile $file): string
    {
        $oldPath = $karyawan->foto;
        $newPath = $file->store('foto-profil', 'public');

        if (! $newPath) {
            throw new \RuntimeException('Foto profil gagal disimpan.');
        }

        try {
            if (! $karyawan->update(['foto' => $newPath])) {
                throw new \RuntimeException('Data foto profil gagal diperbarui.');
            }
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($newPath);
            throw $exception;
        }

        if ($oldPath) {
            self::deleteLegacyPath($oldPath);
        }

        return $newPath;
    }

    public static function deleteLegacyPath(string $path): void
    {
        // Older photos were written to the private disk; newer ones to the
        // public disk. Clean up whichever copy exists.
        Storage::disk('public')->delete($path);
        Storage::disk('private')->delete($path);
    }
}
