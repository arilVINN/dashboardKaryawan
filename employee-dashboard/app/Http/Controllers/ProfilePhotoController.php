<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class ProfilePhotoController extends Controller
{
    /**
     * Fallback for profile photos that predate the public-disk migration.
     *
     * The app builds public URLs like /storage/foto-profil/<file>. New uploads
     * are written to the public disk, but older photos still live on the
     * private disk and therefore 404. Serve those legacy files here so the
     * existing URLs keep working. Files that are present on the public disk
     * are normally served by the web server and never reach this route.
     */
    public function show(string $filename)
    {
        if (preg_match('/^[A-Za-z0-9_.-]+$/', $filename) !== 1 || str_contains($filename, '..')) {
            return response('', 404);
        }

        $path = 'foto-profil/'.$filename;
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            $disk = Storage::disk('private');

            if (! $disk->exists($path)) {
                return response('', 404);
            }
        }

        return $disk->response($path, null, [
            'Content-Type' => $this->mime($path),
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function mime(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };
    }
}
