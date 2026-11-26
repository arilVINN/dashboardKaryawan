<?php

namespace App\Support;

use App\Models\User2;

/**
 * Single source of truth for who may manage employee data.
 *
 * HRD/admin (and the CLI-managed superadmin) may write employee data.
 * Direktur/Direksi are read-only monitors: they may read sensitive data but
 * must not mutate employee records. An "admin" role name is accepted as an
 * alias for HRD-level management.
 */
final class EmployeeAccess
{
    /**
     * Roles allowed to read management/sensitive data. Direktur/Direksi are
     * included as read-only monitors.
     */
    public const MANAGE_ROLES = ['hrd', 'admin', 'direktur', 'direksi', 'superadmin'];

    /**
     * Roles allowed to actually write master data. Direktur/Direksi are
     * deliberately excluded: they are read-only monitors.
     */
    public const WRITE_ROLES = ['hrd', 'admin', 'superadmin'];

    public static function canManage(?User2 $user): bool
    {
        if (! $user instanceof User2) {
            return false;
        }

        return (bool) array_intersect($user->daftarRole(), self::MANAGE_ROLES);
    }

    public static function canWrite(?User2 $user): bool
    {
        if (! $user instanceof User2) {
            return false;
        }

        return (bool) array_intersect($user->daftarRole(), self::WRITE_ROLES);
    }
}
