<?php

namespace App\Http\Controllers;

use App\Models\SuratIzin;
use App\Models\User2;
use App\Support\FileStorage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class SuratIzinPdfController extends Controller
{
    public function show(Request $request, SuratIzin $suratIzin): Response
    {
        $user = $request->user();

        abort_unless($user instanceof User2 && $this->canView($user, $suratIzin), 403);
        abort_unless(
            $suratIzin->status === 'Disetujui',
            403,
            'Softcopy hanya tersedia setelah pengajuan disetujui HRD.'
        );

        $suratIzin->loadMissing(['karyawan.divisi', 'karyawan.user.role', 'kadiv', 'hrd.karyawan']);

        $pdf = Pdf::loadView('pdf.surat-izin', [
            'suratIzin' => $suratIzin,
            'logoDataUri' => $this->logoDataUri(),
            'signatureDataUri' => $this->signatureDataUri($suratIzin),
            'hrdSignatureDataUri' => $this->hrdSignatureDataUri($suratIzin),
            'showSignature' => true,
        ])->setPaper('a4', 'portrait');

        $filename = 'Form-Pengajuan-Perizinan-'
            .preg_replace('/[^A-Za-z0-9_-]/', '', (string) $suratIzin->id_surat)
            .'.pdf';

        return $request->boolean('preview')
            ? $pdf->stream($filename)
            : $pdf->download($filename);
    }

    private function canView(User2 $user, SuratIzin $suratIzin): bool
    {
        if ($user->isDirektur() || $user->hasRole('hrd') || $user->hasRole('admin')) {
            return true;
        }

        if ($user->karyawan_id_karyawan !== null
            && $suratIzin->karyawan_id_karyawan === $user->karyawan_id_karyawan) {
            return true;
        }

        $divisiId = $user->karyawan?->divisi_id_divisi;

        return $user->hasRole('kadiv')
            && $divisiId !== null
            && $suratIzin->karyawan?->divisi_id_divisi === $divisiId;
    }

    private function logoDataUri(): ?string
    {
        $path = public_path('gambar/silindo.png');

        if (! is_file($path)) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode((string) file_get_contents($path));
    }

    private function signatureDataUri(SuratIzin $suratIzin): ?string
    {
        $path = $suratIzin->kadiv_signature_path;

        if ($path) {
            $disk = FileStorage::diskFor($path);

            if ($disk !== null && Storage::disk($disk)->exists($path)) {
                return 'data:image/png;base64,'.base64_encode(
                    (string) Storage::disk($disk)->get($path)
                );
            }
        }

        // A request that has a recorded Kadiv approver must never fall back to
        // a placeholder signature. Only legacy records approved before this
        // flow use the old placeholder.
        if ($suratIzin->kadiv_id_karyawan !== null) {
            return null;
        }

        $fallback = public_path('gambar/dummy-signature.png');

        if (! is_file($fallback)) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode((string) file_get_contents($fallback));
    }

    private function hrdSignatureDataUri(SuratIzin $suratIzin): ?string
    {
        $path = $suratIzin->hrd_signature_path;

        if (! $path) {
            return null;
        }

        $disk = FileStorage::diskFor($path);

        if ($disk === null) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode(
            (string) Storage::disk($disk)->get($path)
        );
    }
}
