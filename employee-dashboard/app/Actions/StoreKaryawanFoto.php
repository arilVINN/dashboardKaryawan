<?php

namespace App\Actions;

use App\Models\Karyawan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class StoreKaryawanFoto
{
    /**
     * Replace the karyawan's profile photo, deleting the previous file.
     * Returns the stored path (e.g. "foto-profil/abc123.jpg").
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
            Storage::disk('public')->delete($oldPath);
        }

        return $newPath;
    }
}
