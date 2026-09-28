<?php

namespace App\Listeners;

use App\Events\TugasDitugaskan;
use App\Models\Notifikasi;
use App\Models\User2;

class BuatNotifikasiTugas
{
    public function handle(TugasDitugaskan $event): void
    {
        $penerima = User2::where(
            'karyawan_id_karyawan',
            $event->tugas->karyawan_id_karyawan
        )->first();

        if (! $penerima) {
            return;
        }

        Notifikasi::create([
            'id_notifikasi' => $this->generateNotifikasiId(),
            'judul_notifikasi' => 'Tugas Baru',
            'isi_notif' => 'Anda mendapat tugas baru: '.$event->tugas->judul_tugas,
            'tanggal_notifikasi' => now()->toDateString(),
            'user_id_user' => $penerima->id_user,
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
