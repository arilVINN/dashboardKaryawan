<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Karyawan;
use App\Models\Role;
use App\Support\EmployeeAccess;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class HrdPeranController extends Controller
{
    /**
     * Only direktur/superadmin may assign or remove additional roles.
     */
    public function update(Request $request, string $id)
    {
        $actor = $request->user();
        if (! EmployeeAccess::canManage($actor)
            || (! $actor->hasRole('direktur') && ! $actor->isSuperAdmin())) {
            abort(403, 'Hanya direktur atau superadmin yang dapat mengubah role tambahan.');
        }

        $karyawan = Karyawan::with('user')->where('id_karyawan', $id)->firstOrFail();
        if (! $karyawan->user) {
            throw ValidationException::withMessages(['roles' => ['Karyawan ini belum memiliki akun login.']]);
        }

        $validated = $request->validate([
            'roles' => ['array'],
            'roles.*' => ['string', 'exists:roles,id_role'],
        ]);

        $daftarRole = Role::whereIn('id_role', $validated['roles'] ?? [])->get();

        // Superadmin can never be granted as an additional role, and the
        // primary role is managed through the jabatan mapping, not here.
        $primaryId = $karyawan->user->role_id_role;
        $terpilih = $daftarRole
            ->reject(fn (Role $role) => strtolower((string) $role->nama_role) === 'superadmin')
            ->reject(fn (Role $role) => $role->id_role === $primaryId)
            ->pluck('id_role')
            ->all();

        $sebelum = $karyawan->user->roles()->pluck('roles.id_role')->sort()->values()->all();
        $karyawan->user->roles()->sync($terpilih);
        $sesudah = $karyawan->user->roles()->pluck('roles.id_role')->sort()->values()->all();

        if ($sebelum !== $sesudah) {
            AuditLog::catat($actor->id_user, 'ubah_role_tambahan', 'karyawan', $karyawan->id_karyawan, [
                'username' => $karyawan->user->username,
                'sebelum' => $sebelum,
                'sesudah' => $sesudah,
            ]);
        }

        return redirect()
            ->route('hrd.detailKaryawan', $karyawan->id_karyawan)
            ->with('success', 'Role tambahan berhasil diperbarui.');
    }
}
