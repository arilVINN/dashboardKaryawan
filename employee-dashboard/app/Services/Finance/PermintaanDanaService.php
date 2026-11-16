<?php

namespace App\Services\Finance;

use App\Exceptions\PermintaanSudahDiprosesException;
use App\Models\AuditLog;
use App\Models\KPermintDana;
use App\Models\Notifikasi;
use App\Models\User2;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Keputusan atas permintaan dana: approve/reject dengan lock transaksi,
 * pemeriksaan status terbaru, policy, dan audit log.
 */
class PermintaanDanaService
{
    public function approve(string $id, User2 $user, ?string $catatan = null): KPermintDana
    {
        return DB::transaction(function () use ($id, $user, $catatan) {
            $permintaan = KPermintDana::with('divisi')->lockForUpdate()->findOrFail($id);

            Gate::forUser($user)->authorize('approve', $permintaan);

            if (! $permintaan->isMenunggu()) {
                throw new PermintaanSudahDiprosesException('Pengajuan ini sudah diproses');
            }

            $alasan = $catatan ?: 'Disetujui melalui konfirmasi finance';

            $permintaan->update([
                'status' => KPermintDana::STATUS_DISETUJUI,
                'tanggal_disetujui' => now()->toDateString(),
                'disetujui_oleh_id_user' => $user->id_user,
                'catatan_finance' => $catatan,
            ]);

            $this->catatAudit($user, $permintaan, KPermintDana::STATUS_DISETUJUI, $alasan, $catatan);
            $this->notifyRequester($permintaan, $user, KPermintDana::STATUS_DISETUJUI, null, $catatan);

            return $permintaan;
        });
    }

    public function reject(string $id, User2 $user, string $alasan, ?string $catatan = null): KPermintDana
    {
        return DB::transaction(function () use ($id, $user, $alasan, $catatan) {
            $permintaan = KPermintDana::with('divisi')->lockForUpdate()->findOrFail($id);

            Gate::forUser($user)->authorize('reject', $permintaan);

            if (! $permintaan->isMenunggu()) {
                throw new PermintaanSudahDiprosesException('Pengajuan ini sudah diproses');
            }

            $permintaan->update([
                'status' => KPermintDana::STATUS_DITOLAK,
                'catatan_finance' => $alasan,
                'disetujui_oleh_id_user' => $user->id_user,
            ]);

            $this->catatAudit($user, $permintaan, KPermintDana::STATUS_DITOLAK, $alasan, $catatan);
            $this->notifyRequester($permintaan, $user, KPermintDana::STATUS_DITOLAK, $alasan, $catatan);

            return $permintaan;
        });
    }

    private function catatAudit(
        User2 $user,
        KPermintDana $permintaan,
        string $statusBaru,
        string $alasan,
        ?string $catatan = null
    ): void {
        $permintaan->loadMissing('divisi');

        AuditLog::catat(
            $user->id_user,
            $statusBaru === KPermintDana::STATUS_DISETUJUI
                ? 'setujui_permintaan_dana'
                : 'tolak_permintaan_dana',
            'permintaan_dana',
            (string) $permintaan->id_kpd,
            [
                'user_id' => $user->id_user,
                'user_nama' => $user->karyawan?->nama ?? $user->username,
                'waktu' => now()->toIso8601String(),
                'keputusan' => $statusBaru,
                'status_lama' => KPermintDana::STATUS_MENUNGGU,
                'status_baru' => $statusBaru,
                'alasan' => $alasan,
                'catatan' => $catatan,
                'nominal' => (float) $permintaan->nominal_diajukan,
                'divisi_id' => $permintaan->divisi_id_divisi,
                'divisi_nama' => $permintaan->divisi?->nama_divisi,
            ]
        );
    }

    /**
     * Kirim notifikasi keputusan ke pengaju, termasuk catatan opsional.
     */
    private function notifyRequester(
        KPermintDana $permintaan,
        User2 $user,
        string $statusBaru,
        ?string $alasan,
        ?string $catatan
    ): void {
        $disetujui = $statusBaru === KPermintDana::STATUS_DISETUJUI;
        $actor = $user->karyawan?->nama ?? $user->username;

        $pesan = 'Pengajuan "'.$permintaan->judul_pengajuan.'" ('.$permintaan->nomor_pengajuan.') '
            .($disetujui ? 'disetujui' : 'ditolak').' oleh '.$actor.'.';

        if (! $disetujui && filled($alasan)) {
            $pesan .= ' Alasan: '.$alasan;
        }

        if (filled($catatan)) {
            $pesan .= ' Catatan: '.$catatan;
        }

        Notifikasi::create([
            'id_notifikasi' => $this->generateNotifikasiId(),
            'judul_notifikasi' => $disetujui ? 'Permintaan Dana Disetujui' : 'Permintaan Dana Ditolak',
            'isi_notif' => $pesan,
            'tanggal_notifikasi' => now()->toDateString(),
            'user_id_user' => $permintaan->user_id_user,
        ]);
    }

    private function generateNotifikasiId(): string
    {
        do {
            $id = 'NTF'.strtoupper(substr(uniqid(), -8));
        } while (Notifikasi::where('id_notifikasi', $id)->exists());

        return $id;
    }
}
