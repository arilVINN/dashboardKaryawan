<?php

namespace App\Services\Finance;

use App\Models\AuditLog;
use App\Models\KeuanganAldiv;
use App\Models\Notifikasi;
use App\Models\User2;
use Illuminate\Support\Facades\DB;

/**
 * Satu pintu untuk pembuatan/perubahan pagu anggaran divisi beserta audit log.
 * Dipakai oleh AlokasiController (route lama) dan komponen Livewire.
 */
class AlokasiPaguService
{
    public function create(array $data, User2 $user): KeuanganAldiv
    {
        return DB::transaction(function () use ($data, $user) {
            $count = KeuanganAldiv::count();

            $alokasi = KeuanganAldiv::create([
                'id_kad' => 'KAD'.str_pad($count + 1, 5, '0', STR_PAD_LEFT),
                'periode' => $data['periode'],
                'nominal_alokasi' => (int) $data['nominal_alokasi'],
                'keterangan' => $data['keterangan'] ?? null,
                'divisi_id_divisi' => $data['divisi_id_divisi'],
                'user_id_user' => $user->id_user,
            ]);

            $alokasi->loadMissing('divisi');

            $this->catat(
                $user,
                'atur_pagu',
                $alokasi,
                null,
                (float) $alokasi->nominal_alokasi,
                $alokasi->keterangan
            );

            $this->notifyPenanggungJawab($alokasi, $user, true);

            return $alokasi;
        });
    }

    public function update(KeuanganAldiv $alokasi, array $data, User2 $user): KeuanganAldiv
    {
        return DB::transaction(function () use ($alokasi, $data, $user) {
            $alokasi->loadMissing('divisi');

            $nominalLama = (float) $alokasi->nominal_alokasi;
            $keteranganLama = $alokasi->keterangan;

            $alokasi->update([
                'nominal_alokasi' => (int) $data['nominal_alokasi'],
                'keterangan' => $data['keterangan'],
            ]);

            $this->catat(
                $user,
                'ubah_pagu',
                $alokasi,
                $nominalLama,
                (float) $alokasi->nominal_alokasi,
                $alokasi->keterangan,
                $keteranganLama
            );

            $this->notifyPenanggungJawab($alokasi, $user, false);

            return $alokasi;
        });
    }

    /**
     * Audit log memuat user, waktu, nilai lama/baru, dan alasan perubahan.
     */
    private function catat(
        User2 $user,
        string $aksi,
        KeuanganAldiv $alokasi,
        ?float $nilaiLama,
        float $nilaiBaru,
        ?string $alasan,
        ?string $keteranganLama = null
    ): void {
        AuditLog::catat(
            $user->id_user,
            $aksi,
            'keuangan_aldiv',
            (string) $alokasi->id_kad,
            [
                'user_id' => $user->id_user,
                'user_nama' => $user->karyawan?->nama ?? $user->username,
                'waktu' => now()->toIso8601String(),
                'divisi_id' => $alokasi->divisi_id_divisi,
                'divisi_nama' => $alokasi->divisi?->nama_divisi,
                'periode' => $alokasi->periode,
                'nilai_lama' => $nilaiLama,
                'nilai_baru' => $nilaiBaru,
                'alasan' => $alasan,
                'keterangan_lama' => $keteranganLama,
                'keterangan_baru' => $alokasi->keterangan,
            ]
        );
    }

    /**
     * Kabari penanggung jawab divisi ketika pagu anggarannya dibuat/diubah.
     */
    private function notifyPenanggungJawab(
        KeuanganAldiv $alokasi,
        User2 $user,
        bool $baru
    ): void {
        $penerima = $alokasi->divisi?->penanggungJawab()?->user;

        if (! $penerima || $penerima->id_user === $user->id_user) {
            return;
        }

        $actor = $user->karyawan?->nama ?? $user->username;
        $nominal = 'Rp '.number_format((float) $alokasi->nominal_alokasi, 0, ',', '.');

        $pesan = 'Pagu anggaran divisi '.$alokasi->divisi?->nama_divisi
            .' periode '.$alokasi->periode
            .' '.($baru ? 'ditetapkan' : 'diperbarui').' oleh '.$actor
            .' menjadi '.$nominal.'.';

        if (filled($alokasi->keterangan)) {
            $pesan .= ' Keterangan: '.$alokasi->keterangan;
        }

        Notifikasi::create([
            'id_notifikasi' => Notifikasi::generateId(),
            'judul_notifikasi' => $baru ? 'Pagu Anggaran Ditetapkan' : 'Pagu Anggaran Diperbarui',
            'isi_notif' => $pesan,
            'tanggal_notifikasi' => now()->toDateString(),
            'user_id_user' => $penerima->id_user,
        ]);
    }
}
