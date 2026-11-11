<?php

namespace App\Policies;

use App\Models\ContentEvent;
use App\Models\User2;

class ContentEventPolicy
{
    public function viewAny(User2 $user): bool
    {
        return true;
    }

    public function view(User2 $user, ContentEvent $event): bool
    {
        return true;
    }

    public function create(User2 $user): bool
    {
        return true;
    }

    public function update(User2 $user, ContentEvent $event): bool
    {
        return $this->canManage($user, $event);
    }

    public function delete(User2 $user, ContentEvent $event): bool
    {
        return $this->canManage($user, $event);
    }

    /**
     * The creator, the Kepala Divisi of the creator's division, and HRD may
     * edit or delete. The event itself no longer stores a division, so the
     * Kadiv check follows the creator's current division.
     */
    private function canManage(User2 $user, ContentEvent $event): bool
    {
        if ((string) $event->created_by === (string) $user->id_user) {
            return true;
        }

        $role = strtolower((string) $user->role?->nama_role);

        if ($role === 'hrd') {
            return true;
        }

        if ($role !== 'kadiv') {
            return false;
        }

        $divisionId = $user->karyawan?->divisi_id_divisi;
        $creatorDivisionId = $event->creator?->karyawan?->divisi_id_divisi;

        return $divisionId !== null
            && $creatorDivisionId !== null
            && (string) $divisionId === (string) $creatorDivisionId;
    }
}
