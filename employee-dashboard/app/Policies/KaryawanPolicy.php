<?php

namespace App\Policies;

use App\Models\Karyawan;
use App\Models\User2;
use App\Support\EmployeeAccess;

class KaryawanPolicy
{
    public function viewAny(User2 $user): bool
    {
        return EmployeeAccess::canManage($user);
    }

    public function view(User2 $user, Karyawan $karyawan): bool
    {
        return EmployeeAccess::canManage($user);
    }

    public function create(User2 $user): bool
    {
        return EmployeeAccess::canManage($user);
    }

    public function update(User2 $user, Karyawan $karyawan): bool
    {
        return EmployeeAccess::canManage($user);
    }

    public function deactivate(User2 $user, Karyawan $karyawan): bool
    {
        return EmployeeAccess::canManage($user);
    }

    public function delete(User2 $user, Karyawan $karyawan): bool
    {
        return EmployeeAccess::canManage($user);
    }

    /**
     * Rekening numbers and attachments are only visible to HRD/admin.
     */
    public function viewSensitive(User2 $user, Karyawan $karyawan): bool
    {
        return EmployeeAccess::canManage($user);
    }
}
