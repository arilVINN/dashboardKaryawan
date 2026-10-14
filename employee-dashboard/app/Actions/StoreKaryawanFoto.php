<?php

namespace App\Actions;

use App\Models\Karyawan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StoreKaryawanFoto
{
    /**
     * Replace the karyawan's profile photo, deleting the previous file.
     * Returns the stored path (e.g. "foto-profil/abc123.jpg").
     */
    public static function handle(Karyawan $karyawan, UploadedFile $file): string
    {
        if ($karyawan->foto) {
            Storage::disk('public')->delete($karyawan->foto);
        }

        $path = $file->store('foto-profil', 'public');
        $karyawan->update(['foto' => $path]);

        return $path;
    }
}
