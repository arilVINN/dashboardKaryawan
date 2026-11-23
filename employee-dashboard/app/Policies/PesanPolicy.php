<?php

namespace App\Policies;

use App\Models\Pesan;
use App\Models\User2;

class PesanPolicy
{
    /**
     * Recipients follow the app's existing rule: staff or kadiv, never the
     * sender, and never an inactive employee. HRD senders are authorised by
     * the route middleware; this policy guards the recipient side.
     */
    public function create(User2 $user, User2 $recipient): bool
    {
        if ($recipient->id_user === $user->id_user) {
            return false;
        }

        $role = strtolower((string) ($recipient->role?->nama_role ?? ''));

        if (! in_array($role, ['staff', 'kadiv'], true)) {
            return false;
        }

        $karyawan = $recipient->karyawan;

        if (! $karyawan) {
            return false;
        }

        $aktif = (bool) ($karyawan->aktif ?? true);
        $status = strtolower((string) ($karyawan->status_aktif ?? 'Aktif'));

        return $aktif && $status !== 'tidak aktif';
    }
}
