<?php

namespace App\Http\Controllers\Hrd;

use App\Actions\StoreKaryawanFoto;
use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\Role;
use App\Models\Tugas;
use App\Services\EmployeeWriteService;
use App\Services\HrdProtectionService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class HrdStaffController extends Controller
{
    public function __construct(private readonly EmployeeWriteService $employeeService) {}

    /**
     * GET /hrd/staff — daftar seluruh karyawan perusahaan.
     */
    public function index()
    {
        $staffs = Karyawan::with(['user.role', 'divisi', 'jabatanRef'])
            ->orderBy('divisi_id_divisi')
            ->orderBy('nama')
            ->get();

        return response()->json([
            'message' => 'Berhasil mengambil daftar seluruh staff',
            'data' => $staffs,
        ]);
    }

    /**
     * POST /hrd/staff — tambah karyawan + akun login dalam satu transaksi.
     */
    public function store(Request $request)
    {
        $payload = $request->all();

        if (isset($payload['tanggal_rekrut']) && ! isset($payload['tanggal_masuk'])) {
            $payload['tanggal_masuk'] = $payload['tanggal_rekrut'];
        }

        // Legacy clients post a plain jabatan string; resolve it to a
        // division-scoped jabatan row before validation.
        if (empty($payload['jabatan_id']) && ! empty($payload['jabatan'])) {
            try {
                $jabatan = $this->employeeService->resolveLegacyJabatan(
                    (string) ($payload['divisi_id_divisi'] ?? ''),
                    (string) $payload['jabatan'],
                );
                $payload['jabatan_id'] = $jabatan->id;
            } catch (ValidationException $e) {
                if ($request->is('api/*')) {
                    throw $e;
                }

                return $this->legacyValidationResponse($e);
            }
        }

        try {
            $result = $this->employeeService->create($payload, $request->user(), false);
        } catch (ValidationException $e) {
            if ($request->is('api/*')) {
                throw $e;
            }

            return $this->legacyValidationResponse($e);
        } catch (QueryException $e) {
            $duplicate = $this->duplicateStaffData($e);
            if ($duplicate !== null) {
                return response()->json($duplicate, 409);
            }
            report($e);

            return response()->json(['message' => 'Gagal menambahkan staff karena kendala pada server. Silakan coba lagi.'], 500);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Gagal menambahkan staff. Silakan coba lagi.'], 500);
        }

        return response()->json([
            'message' => 'Staff dan akun login berhasil dibuat',
            'data' => [
                'karyawan' => $result['karyawan'],
                'user' => $result['user'],
            ],
        ], 201);
    }

    public function show($id)
    {
        $karyawan = Karyawan::with(['user.role', 'divisi', 'jabatanRef', 'tugas.submitTugas', 'lampiran'])
            ->where('id_karyawan', $id)
            ->first();

        if (! $karyawan) {
            return response()->json(['message' => 'Staff tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Berhasil mengambil detail staff',
            'data' => $karyawan,
        ]);
    }

    /**
     * PUT/PATCH /hrd/staff/{id} — ubah profil dan akun login.
     */
    public function update(Request $request, $id)
    {
        $karyawan = Karyawan::with('user')->where('id_karyawan', $id)->first();

        if (! $karyawan) {
            return response()->json(['message' => 'Staff tidak ditemukan'], 404);
        }

        if (HrdProtectionService::isSuperAdminUser($karyawan->user)) {
            return response()->json(['message' => 'Akun superadmin hanya dapat dikelola melalui perintah artisan.'], 403);
        }

        $payload = $request->all();

        if (isset($payload['tanggal_rekrut']) && ! isset($payload['tanggal_masuk'])) {
            $payload['tanggal_masuk'] = $payload['tanggal_rekrut'];
        }

        if (! empty($payload['jabatan']) && empty($payload['jabatan_id'])) {
            try {
                $divisionId = (string) ($payload['divisi_id_divisi'] ?? $karyawan->divisi_id_divisi);
                $jabatan = $this->employeeService->resolveLegacyJabatan($divisionId, (string) $payload['jabatan']);
                $payload['jabatan_id'] = $jabatan->id;
                $payload['divisi_id_divisi'] = $divisionId;
            } catch (ValidationException $e) {
                if ($request->is('api/*')) {
                    throw $e;
                }

                return $this->legacyValidationResponse($e);
            }
        }

        // Keep the existing rule for explicit role assignment.
        if (isset($payload['role_id_role'])) {
            $allowed = Role::all()
                ->filter(fn (Role $role) => in_array(strtolower((string) $role->nama_role), ['kadiv', 'staff', 'hrd'], true))
                ->pluck('id_role');
            if (! $allowed->contains($payload['role_id_role'])) {
                return response()->json([
                    'message' => 'Role akun hanya boleh kadiv, staff, atau hrd',
                    'errors' => ['role_id_role' => ['Role akun hanya boleh kadiv, staff, atau hrd']],
                ], 422);
            }
        }

        try {
            $updated = $this->employeeService->update($karyawan, $payload, $request->user(), false);
        } catch (ValidationException $e) {
            if ($request->is('api/*')) {
                throw $e;
            }

            return $this->legacyValidationResponse($e);
        } catch (QueryException $e) {
            $duplicate = $this->duplicateStaffData($e);
            if ($duplicate !== null) {
                return response()->json($duplicate, 409);
            }
            report($e);

            return response()->json(['message' => 'Gagal mengubah data staff karena kendala pada server. Silakan coba lagi.'], 500);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Gagal mengubah data staff. Silakan coba lagi.'], 500);
        }

        return response()->json([
            'message' => 'Staff berhasil diubah',
            'data' => $updated,
        ]);
    }

    public function updateFoto(Request $request, $id)
    {
        $request->validate([
            'foto' => [
                'required',
                'file',
                'mimetypes:'.implode(',', config('employee.photo.types')),
                'max:'.config('employee.photo.max_kb'),
            ],
        ]);

        $karyawan = Karyawan::where('id_karyawan', $id)->first();

        if (! $karyawan) {
            return response()->json(['message' => 'Staff tidak ditemukan'], 404);
        }

        if (HrdProtectionService::isSuperAdminUser($karyawan->user)) {
            return response()->json(['message' => 'Akun superadmin hanya dapat dikelola melalui perintah artisan.'], 403);
        }

        $path = StoreKaryawanFoto::handle($karyawan, $request->file('foto'));

        return response()->json([
            'message' => 'Foto karyawan berhasil diperbarui.',
            'foto_url' => '/storage/'.$path,
            'path' => $path,
        ]);
    }

    public function destroy($id)
    {
        $karyawan = Karyawan::with('user')->where('id_karyawan', $id)->first();

        if (! $karyawan) {
            return response()->json(['message' => 'Staff tidak ditemukan'], 404);
        }

        $authUser = auth()->user();
        if ($errorMsg = HrdProtectionService::validateDeletion($authUser, $karyawan)) {
            return response()->json(['message' => $errorMsg], 403);
        }

        $hasUnfinishedTask = Tugas::where('karyawan_id_karyawan', $karyawan->id_karyawan)
            ->whereNotIn('status', [Tugas::STATUS_SUDAH_ACC])
            ->exists();

        if ($hasUnfinishedTask) {
            return response()->json([
                'message' => 'Karyawan tidak dapat dihapus karena masih memiliki tugas yang belum selesai (belum di-acc).',
            ], 409);
        }

        try {
            $fotoPath = $karyawan->foto;
            $lampiranPaths = $karyawan->lampiran()->pluck('path_file')->all();

            DB::transaction(function () use ($karyawan): void {
                Tugas::where('karyawan_id_karyawan', $karyawan->id_karyawan)
                    ->each(function ($tugas): void {
                        $tugas->submitTugas()->delete();
                        $tugas->pesans()->delete();
                        $tugas->delete();
                    });

                if ($karyawan->user) {
                    $karyawan->user->pesanDikirim()->delete();
                    $karyawan->user->pesanDiterima()->delete();
                    $karyawan->user->notifikasis()->delete();
                    $karyawan->user->delete();
                }

                $karyawan->lampiran()->delete();
                $karyawan->delete();
            });

            foreach (array_merge([$fotoPath], $lampiranPaths) as $path) {
                if ($path) {
                    Storage::disk('private')->delete($path);
                    Storage::disk('public')->delete($path);
                }
            }
        } catch (QueryException $e) {
            report($e);

            return response()->json([
                'message' => 'Staff tidak dapat dihapus karena masih memiliki data terkait yang tidak dapat dihapus otomatis.',
            ], 409);
        }

        return response()->json(['message' => 'Staff dan akun login berhasil dihapus']);
    }

    /**
     * Keep legacy error keys (e.g. `jabatan`) while the new form uses
     * `jabatan_id`.
     */
    private function legacyValidationResponse(ValidationException $e): JsonResponse
    {
        $errors = $e->errors();

        if (isset($errors['jabatan_id'])) {
            $errors['jabatan'] = array_map(
                fn (string $message) => str_contains($message, 'wajib') ? 'Jabatan wajib dipilih.' : $message,
                $errors['jabatan_id'],
            );
            unset($errors['jabatan_id']);
        }

        return response()->json([
            'message' => 'Data belum bisa disimpan.',
            'errors' => $errors,
        ], 422);
    }

    private function duplicateStaffData(QueryException $e): ?array
    {
        $sqlState = (string) $e->getCode();
        $message = mb_strtolower($e->getMessage());

        $isDuplicate = in_array($sqlState, ['23000', '23505'], true)
            || str_contains($message, 'duplicate key')
            || str_contains($message, 'unique constraint')
            || str_contains($message, 'duplicate entry');

        if (! $isDuplicate) {
            return null;
        }

        if (str_contains($message, 'username')) {
            return [
                'message' => 'Username sudah digunakan. Silakan pilih username lain.',
                'errors' => ['username' => ['Username sudah digunakan. Silakan pilih username lain.']],
            ];
        }

        if (str_contains($message, 'email')) {
            return [
                'message' => 'Email sudah digunakan karyawan lain. Gunakan email lain atau kosongkan.',
                'errors' => ['email' => ['Email sudah digunakan karyawan lain.']],
            ];
        }

        return ['message' => 'ID karyawan atau akun bentrok dengan data lain. Silakan coba lagi.'];
    }
}
