<?php

namespace App\Http\Controllers\Kadiv;

use App\Actions\NotifyHrdUsers;
use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\SuratIzin;
use App\Models\User2;
use App\Services\SignatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KadivSuratIzinController extends Controller
{
    public function __construct(private SignatureService $signatures) {}

    public function index()
    {
        $kadiv = $this->kadiv();

        $suratIzin = SuratIzin::with(['karyawan.divisi'])
            ->where('karyawan_id_karyawan', '!=', $kadiv->id_karyawan)
            ->whereHas('karyawan', fn ($query) => $query
                ->where('divisi_id_divisi', $kadiv->divisi_id_divisi))
            ->latest()
            ->paginate(10);

        $pendingApprovals = SuratIzin::query()
            ->where('status', 'Pending')
            ->where('tahap', 'kadiv')
            ->where('karyawan_id_karyawan', '!=', $kadiv->id_karyawan)
            ->whereHas('karyawan', fn ($query) => $query
                ->where('divisi_id_divisi', $kadiv->divisi_id_divisi))
            ->count();

        $signatureAvailable = $this->signatures->pathFor($kadiv) !== null;

        $suratIzinStatus = SuratIzin::query()
            ->where('karyawan_id_karyawan', $kadiv->id_karyawan)
            ->latest()
            ->get();

        return view('kadiv.suratIzin', compact(
            'suratIzin',
            'suratIzinStatus',
            'pendingApprovals',
            'kadiv',
            'signatureAvailable'
        ));
    }

    public function approve(SuratIzin $suratIzin)
    {
        $kadiv = $this->kadiv();
        $suratIzin->loadMissing('karyawan');

        abort_unless(
            $suratIzin->karyawan?->divisi_id_divisi === $kadiv->divisi_id_divisi,
            403,
            'Anda tidak berwenang menyetujui pengajuan dari divisi lain.'
        );
        abort_unless(
            $suratIzin->status === 'Pending' && $suratIzin->tahap === 'kadiv',
            409,
            'Pengajuan ini sudah diproses atau belum berada pada tahap Kepala Divisi.'
        );

        $snapshotPath = $this->signatures->snapshot($kadiv, $suratIzin->id_surat);

        abort_unless(
            $snapshotPath !== null,
            409,
            'Unggah tanda tangan PNG terlebih dahulu sebelum menyetujui pengajuan.'
        );

        try {
            $processed = DB::transaction(function () use ($suratIzin, $kadiv, $snapshotPath): bool {
                $fresh = SuratIzin::query()
                    ->whereKey($suratIzin->getKey())
                    ->lockForUpdate()
                    ->first();

                if (! $fresh || ! $this->canHandle($fresh, $kadiv)) {
                    return false;
                }

                $fresh->update([
                    'tahap' => 'hrd',
                    'kadiv_id_karyawan' => $kadiv->id_karyawan,
                    'kadiv_signature_path' => $snapshotPath,
                    'kadiv_approved_at' => now(),
                ]);

                return true;
            });
        } catch (\Throwable $e) {
            Storage::disk('private')->delete($snapshotPath);
            report($e);

            return back()->with('error', 'Gagal menyetujui surat izin. Silakan coba lagi.');
        }

        if (! $processed) {
            Storage::disk('private')->delete($snapshotPath);
            abort(409, 'Pengajuan ini sudah diproses.');
        }

        $suratIzin->refresh();

        try {
            app(NotifyHrdUsers::class)(
                'Surat Izin Menunggu Persetujuan HRD',
                'Surat izin "'.$suratIzin->judul_surat.'" ('.$suratIzin->jenis_izin.') dari '
                    .($suratIzin->karyawan?->nama ?? 'karyawan').' telah disetujui Kepala Divisi '
                    .'dan menunggu persetujuan HRD.'
            );
        } catch (\Throwable $e) {
            report($e);
            Log::warning('Notifikasi HRD setelah persetujuan Kadiv gagal dikirim', [
                'surat_izin' => $suratIzin->id_surat,
                'kadiv' => $kadiv->id_karyawan,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Surat izin disetujui dan diteruskan ke HRD.');
    }

    public function reject(SuratIzin $suratIzin)
    {
        $kadiv = $this->kadiv();
        $suratIzin->loadMissing('karyawan');

        abort_unless(
            $suratIzin->karyawan?->divisi_id_divisi === $kadiv->divisi_id_divisi,
            403,
            'Anda tidak berwenang menolak pengajuan dari divisi lain.'
        );
        abort_unless(
            $suratIzin->status === 'Pending' && $suratIzin->tahap === 'kadiv',
            409,
            'Pengajuan ini sudah diproses atau belum berada pada tahap Kepala Divisi.'
        );

        try {
            $processed = DB::transaction(function () use ($suratIzin, $kadiv): bool {
                $fresh = SuratIzin::query()
                    ->whereKey($suratIzin->getKey())
                    ->lockForUpdate()
                    ->first();

                if (! $fresh || ! $this->canHandle($fresh, $kadiv)) {
                    return false;
                }

                $fresh->update([
                    'status' => 'Ditolak',
                    'tahap' => 'selesai',
                    'kadiv_id_karyawan' => $kadiv->id_karyawan,
                    'kadiv_rejected_at' => now(),
                ]);

                return true;
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal menolak surat izin. Silakan coba lagi.');
        }

        abort_unless($processed, 409, 'Pengajuan ini sudah diproses.');

        $suratIzin->refresh();
        $this->notifyRequester(
            $suratIzin,
            'Surat Izin Ditolak',
            'Surat izin "'.$suratIzin->judul_surat.'" ditolak oleh Kepala Divisi.'
        );

        return back()->with('success', 'Surat izin ditolak.');
    }

    public function uploadSignature(Request $request)
    {
        $kadiv = $this->kadiv();

        $request->validate([
            'tanda_tangan' => [
                'nullable',
                'file',
                'mimes:png,jpg,jpeg',
                'max:5120',
                'required_without:tanda_tangan_data',
            ],
            'tanda_tangan_data' => [
                'nullable',
                'string',
                'max:8000000',
                'required_without:tanda_tangan',
                'regex:/^data:image\/png;base64,[A-Za-z0-9+\/=\r\n]+$/',
            ],
        ]);

        try {
            if ($request->hasFile('tanda_tangan')) {
                $this->signatures->store($request->file('tanda_tangan'), $kadiv);
            } else {
                $this->signatures->storeDataUri(
                    (string) $request->input('tanda_tangan_data'),
                    $kadiv
                );
            }
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors([
                'tanda_tangan' => 'Tanda tangan tidak valid. Gunakan PNG/JPG yang jelas atau gambar ulang tanda tangan.',
            ]);
        }

        return back()->with('success', 'Tanda tangan berhasil diunggah.');
    }

    public function showSignature(): StreamedResponse
    {
        $kadiv = $this->kadiv();
        $path = $this->signatures->pathFor($kadiv);

        abort_unless($path !== null, 404);

        return Storage::disk('private')->response($path, 'tanda-tangan.png', [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }

    private function kadiv(): Karyawan
    {
        $user = auth()->user();

        abort_unless(
            $user instanceof User2 && $user->hasRole('kadiv') && $user->karyawan,
            403
        );

        return $user->karyawan;
    }

    private function canHandle(SuratIzin $suratIzin, Karyawan $kadiv): bool
    {
        return $suratIzin->status === 'Pending'
            && $suratIzin->tahap === 'kadiv'
            && $suratIzin->karyawan?->divisi_id_divisi === $kadiv->divisi_id_divisi;
    }
}
