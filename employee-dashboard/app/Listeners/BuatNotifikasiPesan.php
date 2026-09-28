<?php

namespace App\Listeners;

use App\Events\PesanDikirim;
use App\Models\Notifikasi;
use App\Models\User2;

class BuatNotifikasiPesan
{
    public function handle(PesanDikirim $event): void
    {
        $pesan = $event->pesan;

        $pesan->loadMissing([
            'tugas',
            'pengirim.role',
        ]);

        $tugas = $pesan->tugas;
        $pengirim = $pesan->pengirim;

        if (! $tugas || ! $pengirim) {
            return;
        }

        // Hanya buat notifikasi ketika pengirim adalah Kadiv.
        if (strtolower($pengirim->role->nama_role ?? '') !== 'kadiv') {
            return;
        }

        $penerima = User2::where(
            'karyawan_id_karyawan',
            $tugas->karyawan_id_karyawan
        )->first();

        if (! $penerima) {
            return;
        }

        Notifikasi::create([
            'id_notifikasi' => $this->generateNotifikasiId(),
            'judul_notifikasi' => 'Pesan Baru',
            'isi_notif' => 'Ada pesan baru pada tugas: '.$tugas->judul_tugas,
            'tanggal_notifikasi' => now()->toDateString(),
            'user_id_user' => $penerima->id_user,
        ]);
    }

    private function generateNotifikasiId(): string
    {
        do {
            $id = 'NTF'.strtoupper(substr(uniqid(), -8));
        } while (
            Notifikasi::where('id_notifikasi', $id)->exists()
        );

        return $id;
    }
}
