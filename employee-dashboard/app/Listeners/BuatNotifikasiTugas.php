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
            'isi_notif' => $event->tugas->judul_tugas,
            'tugas_id_tugas' => $event->tugas->id_tugas,
            'tanggal_notifikasi' => now()->toDateString(),
            'user_id_user' => $penerima->id_user,
        ]);

        // 2. Kirim email notifikasi ke karyawan. Default-nya dikirim langsung
        //    (sendNow) supaya tetap sampai tanpa queue worker; set
        //    MAIL_QUEUE_NOTIFICATIONS=true bila worker sudah berjalan.
        $emailTujuan = $penerima->karyawan?->email;
        if (! empty($emailTujuan)) {
            $mailable = new TugasMail($event->tugas);

            try {
                if (config('mail.queue_notifications', false)) {
                    Mail::to($emailTujuan)->queue($mailable);
                } else {
                    Mail::to($emailTujuan)->sendNow($mailable);
                }
            } catch (\Throwable $e) {
                Log::error('Gagal mengirim email notifikasi tugas: '.$e->getMessage(), [
                    'id_tugas' => $event->tugas->id_tugas ?? null,
                    'penerima' => $emailTujuan,
                ]);
            }
        } else {
            Log::info('Email notifikasi tugas dilewati: karyawan tidak memiliki email.', [
                'id_tugas' => $event->tugas->id_tugas ?? null,
                'karyawan_id_karyawan' => $event->tugas->karyawan_id_karyawan,
            ]);
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
