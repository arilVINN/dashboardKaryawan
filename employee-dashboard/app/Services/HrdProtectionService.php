<?php

namespace App\Services;

use App\Models\Karyawan;
use App\Models\Role;
use App\Models\User2;

class HrdProtectionService
{
    /**
     * Hitung jumlah user HRD aktif (karyawan belum keluar dan aktif).
     */
    public static function countActiveHrd(): int
    {
        return User2::whereHas('role', function ($q) {
            $q->whereRaw('LOWER(nama_role) = ?', ['hrd']);
        })->whereHas('karyawan', function ($q) {
            $q->whereNull('tanggal_keluar')
              ->where(function ($k) {
                  $k->where('aktif', true)
                    ->orWhere('status_aktif', 'Aktif');
              });
        })->count();
    }

    /**
     * Cek apakah user target merupakan akun HRD.
     */
    public static function isHrdUser(?User2 $user): bool
    {
        if (! $user) {
            return false;
        }

        return strtolower($user->role?->nama_role ?? '') === 'hrd';
    }

    /**
     * Validasi sebelum menghapus karyawan.
     * Mengembalikan pesan error jika melanggar proteksi, atau null jika lolos.
     */
    public static function validateDeletion(?User2 $authUser, Karyawan $karyawan): ?string
    {
        $targetUser = $karyawan->user;

        if ($authUser && $targetUser && $authUser->id_user === $targetUser->id_user) {
            return 'Anda tidak dapat menghapus akun Anda sendiri.';
        }

        if (self::isHrdUser($targetUser)) {
            $activeHrdCount = self::countActiveHrd();
            if ($activeHrdCount <= 1) {
                return 'Tidak dapat menghapus akun HRD terakhir. Harus ada minimal 1 HRD aktif di sistem.';
            }
        }

        return null;
    }

    /**
     * Validasi sebelum menonaktifkan / menandai keluar karyawan.
     */
    public static function validateDeactivation(?User2 $authUser, Karyawan $karyawan): ?string
    {
        $targetUser = $karyawan->user;

        if ($authUser && $targetUser && $authUser->id_user === $targetUser->id_user) {
            return 'Anda tidak dapat menonaktifkan akun Anda sendiri.';
        }

        if (self::isHrdUser($targetUser)) {
            $activeHrdCount = self::countActiveHrd();
            if ($activeHrdCount <= 1) {
                return 'Tidak dapat menonaktifkan akun HRD terakhir. Harus ada minimal 1 HRD aktif di sistem.';
            }
        }

        return null;
    }

    /**
     * Validasi sebelum mengubah role HRD ke role lain.
     */
    public static function validateRoleChange(?User2 $authUser, Karyawan $karyawan, string $newRoleId): ?string
    {
        $targetUser = $karyawan->user;
        if (! $targetUser) {
            return null;
        }

        if ($targetUser->role_id_role === $newRoleId) {
            return null;
        }

        $newRole = Role::find($newRoleId);
        $newRoleName = strtolower($newRole?->nama_role ?? '');

        // Jika user target saat ini adalah HRD dan role barunya BUKAN HRD
        if (self::isHrdUser($targetUser) && $newRoleName !== 'hrd') {
            if ($authUser && $authUser->id_user === $targetUser->id_user) {
                return 'Anda tidak dapat mengubah role akun Anda sendiri.';
            }

            $activeHrdCount = self::countActiveHrd();
            if ($activeHrdCount <= 1) {
                return 'Tidak dapat mengubah role akun HRD terakhir. Harus ada minimal 1 HRD aktif pengganti di sistem.';
            }
        }

        return null;
    }
}
