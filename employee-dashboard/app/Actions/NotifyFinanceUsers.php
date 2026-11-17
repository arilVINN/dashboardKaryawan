<?php

namespace App\Actions;

use App\Models\Divisi;
use App\Models\Notifikasi;
use App\Models\User2;
use Illuminate\Database\Eloquent\Collection;

/**
 * Kirim notifikasi masuk ke semua Kadiv Finance & Accounting.
 *
 * Dipakai oleh alur pengajuan dana dan laporan laba/rugi agar Finance tidak
 * perlu membuka halaman daftar berulang kali untuk mengetahui ada request baru.
 */
class NotifyFinanceUsers
{
    public function __invoke(string $judul, string $isi): void
    {
        foreach ($this->recipients() as $user) {
            Notifikasi::create([
                'id_notifikasi' => Notifikasi::generateId(),
                'judul_notifikasi' => $judul,
                'isi_notif' => $isi,
                'tanggal_notifikasi' => now()->toDateString(),
                'user_id_user' => $user->id_user,
            ]);
        }
    }

    private function recipients(): Collection
    {
        return User2::query()
            ->whereHas('role', fn ($query) => $query
                ->whereRaw('LOWER(nama_role) = ?', ['kadiv']))
            ->whereHas('karyawan.divisi', fn ($query) => $query
                ->where('kode_divisi', Divisi::KODE_FINANCE))
            ->get();
    }
}
