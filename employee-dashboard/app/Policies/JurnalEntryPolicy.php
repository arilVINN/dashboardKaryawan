<?php

namespace App\Policies;

use App\Models\User2;

/**
 * Hak akses Jurnal Umum mengikuti hak untuk membuka Rekap Laba/Rugi
 * (Kadiv Finance; Direktur/Direksi/Superadmin mewarisi akses finance).
 */
class JurnalEntryPolicy
{
    public function viewAny(User2 $user): bool
    {
        return $user->isKadivFinance();
    }

    public function view(User2 $user): bool
    {
        return $user->isKadivFinance();
    }

    public function create(User2 $user): bool
    {
        return $user->isKadivFinance();
    }

    public function cancel(User2 $user): bool
    {
        return $user->isKadivFinance();
    }

    public function update(User2 $user): bool
    {
        return $user->isKadivFinance();
    }

    public function delete(User2 $user): bool
    {
        return $user->isKadivFinance();
    }
}
