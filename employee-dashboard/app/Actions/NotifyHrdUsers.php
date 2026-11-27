<?php

namespace App\Actions;

use App\Models\Notifikasi;
use App\Models\User2;
use Illuminate\Database\Eloquent\Collection;

/**
 * Kirim notifikasi permintaan masuk (booking / surat izin) ke semua HRD.
 */
class NotifyHrdUsers
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
            ->where(function ($query) {
                $query->whereHas('role', fn ($roleQuery) => $roleQuery
                    ->whereRaw('LOWER(nama_role) = ?', ['hrd']))
                    ->orWhereHas('roles', fn ($roleQuery) => $roleQuery
                        ->whereRaw('LOWER(nama_role) = ?', ['hrd']));
            })
            ->distinct()
            ->get();
    }
}
