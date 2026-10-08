<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Karyawan;
use App\Models\User2;
use App\Models\Divisi;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;

class HrdStaffController extends Controller
{
    /**
     * GET /api/hrd/staff
     * Daftar seluruh karyawan perusahaan.
     */
    public function index()
    {
        $staffs = Karyawan::with(['user.role', 'divisi'])
            ->orderBy('divisi_id_divisi')
            ->orderBy('nama')
            ->get();

        return response()->json([
            'message' => 'Berhasil mengambil daftar seluruh staff',
            'data' => $staffs
        ]);
    }

    /**
     * POST /api/hrd/staff
     * Menambahkan data karyawan/staff baru + buatkan akun login.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'tanggal_lahir' => 'nullable|date',
            'tanggal_rekrut' => 'nullable|date',
            'no_telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100|unique:karyawans,email',
            'jabatan' => 'required|string|max:100',
            'divisi_id_divisi' => 'required|string|max:20',
            // Data akun login
            'username' => 'required|string|max:50|unique:users2,username',
            'password' => 'required|string|min:6',
            'role_id_role' => 'nullable|string|exists:roles,id_role', // Opsional, default staff
        ]);

        // Try-catch: pastikan divisi ada
        try {
            $divisi = Divisi::where('id_divisi', $request->divisi_id_divisi)->first();

            if (!$divisi) {
                return response()->json([
                    'message' => 'Divisi dengan ID "' . $request->divisi_id_divisi . '" tidak ditemukan. Silakan buat divisi terlebih dahulu.'
                ], 404);
            }

            $jabatanRole = $this->roleNameForJabatan($request->jabatan);
            $roleId = $jabatanRole
                ? Role::whereRaw('LOWER(nama_role) = ?', [$jabatanRole])->value('id_role')
                : ($request->role_id_role
                    ?? Role::whereRaw('LOWER(nama_role) = ?', ['staff'])->value('id_role'));

            if (!$roleId) {
                return response()->json([
                    'message' => 'Role ' . ($jabatanRole ?? 'staff') . ' belum tersedia. Tambahkan role tersebut terlebih dahulu.'
                ], 422);
            }

            // Gunakan transaksi agar data karyawan + user konsisten
            DB::beginTransaction();

            // 1. Generate ID Karyawan (KRxxx)
            $lastKaryawan = Karyawan::orderBy('id_karyawan', 'desc')->first();
            $newKaryawanId = $lastKaryawan
                ? 'KR' . str_pad(intval(substr($lastKaryawan->id_karyawan, 2)) + 1, 3, '0', STR_PAD_LEFT)
                : 'KR001';

            // 2. Buat Karyawan
            $karyawan = Karyawan::create([
                'id_karyawan' => $newKaryawanId,
                'nama' => $request->nama,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tanggal_lahir' => $request->tanggal_lahir,
                'tanggal_rekrut' => $request->tanggal_rekrut ?? now()->toDateString(),
                'no_telepon' => $request->no_telepon,
                'email' => $request->email,
                'jabatan' => $request->jabatan,
                'divisi_id_divisi' => $request->divisi_id_divisi,
            ]);

            // 3. Generate ID User (USxxx)
            $lastUser = User2::orderBy('id_user', 'desc')->first();
            $newUserId = $lastUser
                ? 'US' . str_pad(intval(substr($lastUser->id_user, 2)) + 1, 3, '0', STR_PAD_LEFT)
                : 'US001';

            // 4. Default role = staff jika tidak diisi
            // 5. Buat Akun User
            $user = User2::create([
                'id_user' => $newUserId,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role_id_role' => $roleId,
                'karyawan_id_karyawan' => $karyawan->id_karyawan,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Staff dan akun login berhasil dibuat',
                'data' => [
                    'karyawan' => $karyawan,
                    'user' => [
                        'id_user' => $user->id_user,
                        'username' => $user->username,
                        'role' => $roleId,
                    ]
                ]
            ], 201);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            report($e);
            // Duplikat race-condition (username/ID): 23000 integrity violation
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Staff dengan username tersebut sudah ada'
                ], 409);
            }
            return response()->json([
                'message' => 'Gagal menambahkan staff. Silakan coba lagi.'
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'message' => 'Gagal menambahkan staff. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * GET /api/hrd/staff/{id_karyawan}
     * Detail profil staff + rincian tugas (read-only).
     */
    public function show($id)
    {
        $karyawan = Karyawan::with(['user.role', 'divisi', 'tugas.submitTugas'])
            ->where('id_karyawan', $id)
            ->first();

        if (!$karyawan) {
            return response()->json(['message' => 'Staff tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Berhasil mengambil detail staff',
            'data' => $karyawan
        ]);
    }

    /**
     * PUT/PATCH /api/hrd/staff/{id_karyawan}
     * Mengubah profil staff dan akun login.
     */
    public function update(Request $request, $id)
    {
        $karyawan = Karyawan::with('user')->where('id_karyawan', $id)->first();

        if (!$karyawan) {
            return response()->json(['message' => 'Staff tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'nama' => 'sometimes|required|string|max:100',
            'jenis_kelamin' => 'sometimes|required|in:Laki-laki,Perempuan',
            'tanggal_lahir' => 'sometimes|nullable|date',
            'tanggal_rekrut' => 'sometimes|nullable|date',
            'no_telepon' => 'sometimes|nullable|string|max:20',
            'email' => [
                'sometimes', 'nullable', 'email', 'max:100',
                Rule::unique('karyawans', 'email')->ignore($karyawan->id_karyawan, 'id_karyawan'),
            ],
            'jabatan' => 'sometimes|required|string|max:100',
            'divisi_id_divisi' => 'sometimes|required|string|max:20|exists:divisis,id_divisi',
            'username' => [
                'sometimes', 'required', 'string', 'max:50',
                Rule::unique('users2', 'username')->ignore($karyawan->user?->id_user, 'id_user'),
            ],
            'password' => 'sometimes|required|string|min:6',
            'role_id_role' => 'sometimes|required|string|exists:roles,id_role',
        ]);

        if (isset($validated['role_id_role'])) {
            $role = Role::where('id_role', $validated['role_id_role'])->first();

            if (!in_array(strtolower($role->nama_role), ['kadiv', 'staff'], true)) {
                return response()->json([
                    'message' => 'Role staff hanya boleh kadiv atau staff',
                    'errors' => ['role_id_role' => ['Role staff hanya boleh kadiv atau staff']],
                ], 422);
            }
        }

        if (isset($validated['jabatan'])) {
            $jabatanRole = $this->roleNameForJabatan($validated['jabatan']);

            if ($jabatanRole && $karyawan->user) {
                $roleId = Role::whereRaw('LOWER(nama_role) = ?', [$jabatanRole])->value('id_role');

                if (!$roleId) {
                    return response()->json([
                        'message' => 'Role ' . $jabatanRole . ' belum tersedia. Tambahkan role tersebut terlebih dahulu.',
                        'errors' => ['jabatan' => ['Role ' . $jabatanRole . ' belum tersedia.']],
                    ], 422);
                }

                $validated['role_id_role'] = $roleId;
            }
        }

        if (array_intersect(['username', 'password', 'role_id_role'], array_keys($validated)) && !$karyawan->user) {
            return response()->json(['message' => 'Akun login staff tidak ditemukan'], 409);
        }

        DB::transaction(function () use ($karyawan, $validated): void {
            $karyawan->update(array_intersect_key($validated, array_flip([
                'nama', 'jenis_kelamin', 'tanggal_lahir', 'tanggal_rekrut',
                'no_telepon', 'email', 'jabatan', 'divisi_id_divisi',
            ])));

            if ($karyawan->user) {
                $accountChanges = array_intersect_key($validated, array_flip(['username', 'role_id_role']));
                if (isset($validated['password'])) {
                    $accountChanges['password'] = Hash::make($validated['password']);
                }
                if ($accountChanges) {
                    $karyawan->user->update($accountChanges);
                }
            }
        });

        return response()->json([
            'message' => 'Staff berhasil diubah',
            'data' => $karyawan->fresh(['user.role', 'divisi']),
        ]);
    }

    /**
     * DELETE /api/hrd/staff/{id_karyawan}
     * Menghapus profil staff beserta akun login jika tidak ada data terkait.
     */
    public function destroy($id)
    {
        $karyawan = Karyawan::with('user')->where('id_karyawan', $id)->first();

        if (!$karyawan) {
            return response()->json(['message' => 'Staff tidak ditemukan'], 404);
        }

        try {
            DB::transaction(function () use ($karyawan): void {
                $karyawan->user?->delete();
                $karyawan->delete();
            });
        } catch (QueryException $e) {
            report($e);
            return response()->json([
                'message' => 'Staff tidak dapat dihapus karena masih memiliki tugas atau pesan terkait',
            ], 409);
        }

        return response()->json(['message' => 'Staff dan akun login berhasil dihapus']);
    }

    private function roleNameForJabatan(string $jabatan): ?string
    {
        $normalized = mb_strtolower(trim($jabatan));

        if (preg_match('/^kepala\s+divisi(?:\s|$)/i', $normalized) === 1) {
            return 'kadiv';
        }

        if (in_array($normalized, ['staff', 'wakil kepala divisi'], true)) {
            return 'staff';
        }

        return null;
    }
}
