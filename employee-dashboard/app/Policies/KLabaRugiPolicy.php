<?php

namespace App\Policies;

use App\Models\KLabaRugi;
use App\Models\User2;

class KLabaRugiPolicy
{
    /**
     * Hanya Kadiv Finance yang boleh memverifikasi, dan tidak untuk laporan
     * yang ia kirim sendiri. Status "menunggu" dicek ulang di dalam transaksi.
     */
    public function verify(User2 $user, KLabaRugi $laporan): bool
    {
        return $user->isKadivFinance()
            && $laporan->user_id_user !== $user->id_user;
    }
}
