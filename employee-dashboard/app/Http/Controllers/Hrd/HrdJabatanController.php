<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Divisi;
use App\Models\Jabatan;
use App\Support\EmployeeAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class HrdJabatanController extends Controller
{
    public function store(Request $request)
    {
        Gate::authorize('create', Jabatan::class);

        $validated = $request->validate([
            'division_id' => ['required', 'string', 'exists:divisis,id_divisi'],
            'nama' => ['required', 'string', 'max:80'],
        ], [
            'division_id.required' => 'Divisi wajib dipilih.',
            'nama.required' => 'Nama jabatan wajib diisi.',
            'nama.max' => 'Nama jabatan maksimal 80 karakter.',
        ]);

        $nama = Jabatan::normaliseNama($validated['nama']);
        $this->assertUnique($validated['division_id'], $nama);

        $jabatan = Jabatan::create([
            'division_id' => $validated['division_id'],
            'nama' => $nama,
            'aktif' => true,
        ]);

        AuditLog::catat($request->user()->id_user, 'tambah_jabatan', 'jabatan', (string) $jabatan->id, [
            'division_id' => $validated['division_id'],
            'nama' => $nama,
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
        ], [
            'nama.required' => 'Nama jabatan wajib diisi.',
            'nama.max' => 'Nama jabatan maksimal 80 karakter.',
        ]);

        $nama = Jabatan::normaliseNama($validated['nama']);
        $this->assertUnique($jabatan->division_id, $nama, $jabatan->id);

        $jabatan->update(['nama' => $nama]);

        AuditLog::catat($request->user()->id_user, 'ubah_nama_jabatan', 'jabatan', (string) $jabatan->id, [
            'division_id' => $jabatan->division_id,
        ]);

        return response()->json([
            'message' => 'Nama jabatan berhasil diubah',
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
