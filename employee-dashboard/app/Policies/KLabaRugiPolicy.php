<?php

namespace App\Policies;

use App\Models\KLabaRugi;
use App\Models\User2;

class KLabaRugiPolicy
{
    /**
     * Hanya Kadiv Finance (atau superadmin) yang boleh memverifikasi, dan tidak
     * untuk laporan yang ia kirim sendiri. Direktur/Direksi read-only sehingga
     * tidak boleh memverifikasi. Status "menunggu" dicek ulang di dalam
     * transaksi.
     */
    public function verify(User2 $user, KLabaRugi $laporan): bool
    {
        $role = strtolower(trim((string) $user->role?->nama_role));

        if (in_array($role, ['direktur', 'direksi'], true)) {
            return false;
        }

        return $user->isKadivFinance()
            && $laporan->user_id_user !== $user->id_user;
    }
}
