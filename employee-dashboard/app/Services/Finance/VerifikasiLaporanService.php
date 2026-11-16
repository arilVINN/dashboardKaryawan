<?php

namespace App\Services\Finance;

use App\Exceptions\LaporanSudahDiverifikasiException;
use App\Models\AuditLog;
use App\Models\KLabaRugi;
use App\Models\User2;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Verifikasi laporan laba/rugi: kunci transaksi, cek status terbaru, policy,
 * dan audit log.
 */
class VerifikasiLaporanService
{
    public function verifikasi(string $id, User2 $user, ?string $catatan = null): KLabaRugi
    {
        return $this->putuskan($id, $user, KLabaRugi::STATUS_DISETUJUI, $catatan);
    }

    public function putuskan(string $id, User2 $user, string $status, ?string $catatan = null): KLabaRugi
    {
        return DB::transaction(function () use ($id, $user, $status, $catatan) {
            $laporan = KLabaRugi::with('divisi')->lockForUpdate()->findOrFail($id);

            Gate::forUser($user)->authorize('verify', $laporan);

            if (! $laporan->isMenunggu()) {
                throw new LaporanSudahDiverifikasiException('Laporan ini sudah diverifikasi');
            }

            $status = $status === KLabaRugi::STATUS_PERLU_REVISI
                ? KLabaRugi::STATUS_PERLU_REVISI
                : KLabaRugi::STATUS_DISETUJUI;

            $laporan->update([
                'status_verifikasi' => $status,
                'catatan_verifikasi' => $catatan,
                'diverifikasi_oleh_id_user' => $user->id_user,
            ]);

            $this->catatAudit($user, $laporan, $status, $catatan);

            return $laporan;
        });
    }

    private function catatAudit(
        User2 $user,
        KLabaRugi $laporan,
        string $statusBaru,
        ?string $catatan
    ): void {
        $laporan->loadMissing('divisi');

        AuditLog::catat(
            $user->id_user,
            'verifikasi_laporan_laba_rugi',
            'laporan_laba_rugi',
            (string) $laporan->id_klr,
            [
                'user_id' => $user->id_user,
                'user_nama' => $user->karyawan?->nama ?? $user->username,
                'waktu' => now()->toIso8601String(),
                'laporan_id' => $laporan->id_klr,
                'divisi_id' => $laporan->divisi_id_divisi,
                'divisi_nama' => $laporan->divisi?->nama_divisi,
                'periode' => $laporan->periode,
                'minggu_ke' => $laporan->minggu_ke,
                'status_lama' => KLabaRugi::STATUS_MENUNGGU,
                'status_baru' => $statusBaru,
                'catatan' => $catatan,
                'laba_bersih' => (float) $laporan->laba_bersih,
                'margin_persen' => (float) $laporan->margin_persen,
            ]
        );
    }
}
