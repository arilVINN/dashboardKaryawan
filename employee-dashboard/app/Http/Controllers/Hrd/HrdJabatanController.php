<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\Role;
use App\Support\EmployeeAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HrdJabatanController extends Controller
{
    /**
     * Dedicated HRD page for managing jabatan (create/rename/deactivate).
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Jabatan::class);

        $divisiFilter = trim((string) $request->query('divisi', ''));
        $divisis = Divisi::query()->ordered()->get();

        $jabatans = Jabatan::query()
            ->with(['divisi', 'role'])
            ->withCount(['karyawans as jumlah_karyawan' => fn ($q) => $q->whereNull('tanggal_keluar')])
            ->when($divisiFilter !== '', fn ($q) => $q->where('division_id', $divisiFilter))
            ->orderBy('division_id')
            ->orderBy('nama')
            ->get()
            ->groupBy('division_id');

        $roleOptions = Role::all()
            ->filter(fn (Role $role) => in_array(strtolower((string) $role->nama_role), Jabatan::roleOptions(), true))
            ->sortBy('nama_role')
            ->values();

        return view('hrd.manajemenJabatan', compact(
            'divisis',
            'jabatans',
            'divisiFilter',
            'roleOptions',
        ));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Jabatan::class);

        $validated = $request->validate([
            'division_id' => ['required', 'string', 'exists:divisis,id_divisi'],
            'nama' => ['required', 'string', 'max:80'],
            'role_id_role' => ['required', 'string', Rule::in($this->allowedRoleIds())],
        ], [
            'division_id.required' => 'Divisi wajib dipilih.',
            'nama.required' => 'Nama jabatan wajib diisi.',
            'nama.max' => 'Nama jabatan maksimal 80 karakter.',
            'role_id_role.required' => 'Role akun wajib dipilih.',
        ]);

        $nama = Jabatan::normaliseNama($validated['nama']);
        $this->assertUnique($validated['division_id'], $nama);

        $jabatan = Jabatan::create([
            'division_id' => $validated['division_id'],
            'nama' => $nama,
            'role_id_role' => $validated['role_id_role'],
            'aktif' => true,
        ]);

        AuditLog::catat($request->user()->id_user, 'tambah_jabatan', 'jabatan', (string) $jabatan->id, [
            'division_id' => $validated['division_id'],
            'nama' => $nama,
            'role' => strtolower((string) Role::find($validated['role_id_role'])?->nama_role),
        ]);

        return response()->json([
            'message' => 'Jabatan berhasil ditambahkan',
            'data' => $jabatan,
        ], 201);
    }

    public function update(Request $request, Jabatan $jabatan)
    {
        Gate::authorize('update', $jabatan);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:80'],
            'role_id_role' => ['required', 'string', Rule::in($this->allowedRoleIds())],
        ], [
            'nama.required' => 'Nama jabatan wajib diisi.',
            'nama.max' => 'Nama jabatan maksimal 80 karakter.',
            'role_id_role.required' => 'Role akun wajib dipilih.',
        ]);

        $nama = Jabatan::normaliseNama($validated['nama']);
        $this->assertUnique($jabatan->division_id, $nama, $jabatan->id);

        $jabatan->update(['nama' => $nama, 'role_id_role' => $validated['role_id_role']]);

        AuditLog::catat($request->user()->id_user, 'ubah_nama_jabatan', 'jabatan', (string) $jabatan->id, [
            'division_id' => $jabatan->division_id,
            'role' => strtolower((string) Role::find($validated['role_id_role'])?->nama_role),
        ]);

        return response()->json([
            'message' => 'Jabatan berhasil diubah',
            'data' => $jabatan->fresh(),
        ]);
    }

    public function nonaktifkan(Request $request, Jabatan $jabatan)
    {
        Gate::authorize('deactivate', $jabatan);

        $jabatan->update(['aktif' => false]);

        AuditLog::catat($request->user()->id_user, 'nonaktifkan_jabatan', 'jabatan', (string) $jabatan->id, [
            'division_id' => $jabatan->division_id,
        ]);

        return response()->json([
            'message' => 'Jabatan berhasil dinonaktifkan',
            'data' => $jabatan->fresh(),
        ]);
    }

    private function allowedRoleIds(): array
    {
        return Role::all()
            ->filter(fn (Role $role) => in_array(
                strtolower((string) $role->nama_role),
                Jabatan::roleOptions(),
                true,
            ))
            ->pluck('id_role')
            ->all();
    }

    private function assertUnique(string $divisionId, string $nama, ?int $ignoreId = null): void
    {
        $query = Jabatan::where('division_id', $divisionId)
            ->whereRaw('LOWER(TRIM(nama)) = ?', [mb_strtolower($nama)]);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'nama' => ['Jabatan ini sudah ada di divisi ini'],
            ]);
        }
    }
}
