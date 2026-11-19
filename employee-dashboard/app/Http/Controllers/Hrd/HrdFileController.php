<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\LampiranKaryawan;
use App\Support\EmployeeAccess;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HrdFileController extends Controller
{
    /**
     * Serve a profile photo from private storage. Only HRD/admin may view it.
     */
    public function foto(Karyawan $karyawan): StreamedResponse
    {
        Gate::authorize('viewSensitive', $karyawan);

        $path = $karyawan->foto;
        if (! $path) {
            abort(404);
        }

        $disk = 'private';
        if (! $this->exists($path, $disk)) {
            // Legacy photos live on the public disk; still require authorization.
            $disk = 'public';
            if (! $this->exists($path, $disk)) {
                abort(404);
            }
        }

        return Storage::disk($disk)->response(
            $path,
            'foto-profil.'.$this->extension($path),
            ['Content-Type' => $this->mime($path), 'Cache-Control' => 'private, no-store'],
        );
    }

    /**
     * Serve an attachment from private storage after an authorization check.
     */
    public function lampiran(LampiranKaryawan $lampiran): StreamedResponse
    {
        Gate::authorize('viewSensitive', $lampiran->karyawan);

        $path = $lampiran->path_file;
        $disk = 'private';
        if (! $this->exists($path, $disk)) {
            // Legacy files were stored on the public disk before the private
            // storage migration; keep them readable for authorized roles.
            $disk = 'public';
            if (! $this->exists($path, $disk)) {
                abort(404, 'File lampiran tidak ditemukan di storage.');
            }
        }

        return Storage::disk($disk)->response(
            $path,
            $lampiran->nama_file,
            ['Content-Type' => $this->mime($path), 'Cache-Control' => 'private, no-store'],
        );
    }

    private function exists(string $path, string $disk = 'private'): bool
    {
        try {
            return Storage::disk($disk)->exists($path);
        } catch (\Throwable) {
            return false;
        }
    }

    private function mime(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };
    }

    private function extension(string $path): string
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION)) ?: 'png';
    }
}
