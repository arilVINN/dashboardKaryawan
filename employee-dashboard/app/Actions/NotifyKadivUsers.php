<?php

namespace App\Actions;

use App\Models\Notifikasi;
use App\Models\User2;
use Illuminate\Database\Eloquent\Collection;

/**
 * Kirim notifikasi permintaan surat izin ke Kepala Divisi terkait.
 */
class NotifyKadivUsers
{
    public function __invoke(string $judul, string $isi, ?string $divisiId = null): void
    {
        foreach ($this->recipients($divisiId) as $user) {
            Notifikasi::create([
                'id_notifikasi' => Notifikasi::generateId(),
                'judul_notifikasi' => $judul,
                'isi_notif' => $isi,
                'tanggal_notifikasi' => now()->toDateString(),
                'user_id_user' => $user->id_user,
            ]);
        }
    }

    private function recipients(?string $divisiId): Collection
    {
        $query = User2::query()
            ->where(function ($query) {
                $query->whereHas('role', fn ($roleQuery) => $roleQuery
                    ->whereRaw('LOWER(nama_role) = ?', ['kadiv']))
                    ->orWhereHas('roles', fn ($roleQuery) => $roleQuery
                        ->whereRaw('LOWER(nama_role) = ?', ['kadiv']));
            });

        if ($divisiId !== null) {
            $query->whereHas('karyawan', fn ($query) => $query
                ->where('divisi_id_divisi', $divisiId));
        }

        return $query->distinct()->get();
    }
}
