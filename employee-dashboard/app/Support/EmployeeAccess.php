<?php

namespace App\Support;

use App\Models\User2;

/**
 * Single source of truth for who may manage employee data.
 *
 * The existing roles and permissions are kept as-is: HRD and the director
 * level (including the CLI-managed superadmin) may write employee data. An
 * "admin" role name is accepted as an alias for the director level.
 */
final class EmployeeAccess
{
    public const MANAGE_ROLES = ['hrd', 'admin', 'direktur', 'direksi', 'superadmin'];

    public static function canManage(?User2 $user): bool
    {
        if (! $user instanceof User2) {
            return false;
        }

        $role = strtolower(trim((string) $user->role?->nama_role));

        return in_array($role, self::MANAGE_ROLES, true) || $user->isDirektur();
    }
}
