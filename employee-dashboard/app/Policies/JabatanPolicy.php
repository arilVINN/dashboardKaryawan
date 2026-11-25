<?php

namespace App\Policies;

use App\Models\Jabatan;
use App\Models\User2;
use App\Support\EmployeeAccess;

class JabatanPolicy
{
    public function viewAny(User2 $user): bool
    {
        return EmployeeAccess::canManage($user);
    }

    public function create(User2 $user): bool
    {
        return EmployeeAccess::canWrite($user);
    }

    public function update(User2 $user, Jabatan $jabatan): bool
    {
        return EmployeeAccess::canWrite($user);
    }

    public function deactivate(User2 $user, Jabatan $jabatan): bool
    {
        return EmployeeAccess::canWrite($user);
    }

    public function delete(User2 $user, Jabatan $jabatan): bool
    {
        // A jabatan with employees may only be deactivated, never deleted.
        return EmployeeAccess::canWrite($user)
            && $jabatan->karyawans()->doesntExist();
    }
}
