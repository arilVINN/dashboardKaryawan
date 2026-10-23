<?php

namespace App\Listeners;

use App\Events\TugasDitugaskan;
use App\Mail\TugasMail;
use App\Models\Notifikasi;
use App\Models\User2;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BuatNotifikasiTugas
{
    public function handle(TugasDitugaskan $event): void
    {
        $penerima = User2::with('karyawan')->where(
            'karyawan_id_karyawan',
            $event->tugas->karyawan_id_karyawan
        )->first();

        if (! $penerima) {
            return;
        }

        // 1. Simpan notifikasi ke database (untuk web & toast)
        Notifikasi::create([
            'id_notifikasi' => $this->generateNotifikasiId(),
            'judul_notifikasi' => 'Tugas Baru',
            'isi_notif' => 'Anda mendapat tugas baru: '.$event->tugas->judul_tugas,
            'tanggal_notifikasi' => now()->toDateString(),
            'user_id_user' => $penerima->id_user,
        ]);

        // 2. Kirim email notifikasi ke karyawan
        $emailTujuan = $penerima->karyawan?->email;
        if (! empty($emailTujuan)) {
            try {
                Mail::to($emailTujuan)->send(new TugasMail($event->tugas));
            } catch (\Throwable $e) {
                Log::warning('Gagal mengirim email notifikasi tugas: ' . $e->getMessage());
            }
        }
    }

    private function generateNotifikasiId(): string
    {
        do {
            $id = 'NTF'.strtoupper(substr(uniqid(), -8));
        } while (Notifikasi::where('id_notifikasi', $id)->exists());

        return $id;
    }
}
