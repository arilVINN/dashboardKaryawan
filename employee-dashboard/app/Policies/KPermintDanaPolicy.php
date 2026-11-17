<?php

namespace App\Policies;

use App\Models\KPermintDana;
use App\Models\User2;

class KPermintDanaPolicy
{
    public function approve(User2 $user, KPermintDana $permintaan): bool
    {
        return $this->canDecide($user, $permintaan);
    }

    public function reject(User2 $user, KPermintDana $permintaan): bool
    {
        return $this->canDecide($user, $permintaan);
    }

    /**
     * Hanya Kadiv Finance yang boleh memutuskan, dan tidak untuk pengajuannya
     * sendiri. Status "menunggu" dicek ulang di dalam transaksi action.
     */
    private function canDecide(User2 $user, KPermintDana $permintaan): bool
    {
        return $user->isKadivFinance()
            && $permintaan->user_id_user !== $user->id_user;
    }
}
