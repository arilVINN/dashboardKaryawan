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
            'tanggal_lahir' => 'required|date',
            'tanggal_rekrut' => 'nullable|date',
            'no_telepon' => 'required|string|max:20',
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
                'jabatan' => $request->jabatan,
                'divisi_id_divisi' => $request->divisi_id_divisi,
            ]);

            // 3. Generate ID User (USxxx)
            $lastUser = User2::orderBy('id_user', 'desc')->first();
            $newUserId = $lastUser
                ? 'US' . str_pad(intval(substr($lastUser->id_user, 2)) + 1, 3, '0', STR_PAD_LEFT)
                : 'US001';

            // 4. Default role = staff jika tidak diisi
            $roleId = $request->role_id_role ?? Role::where('nama_role', 'staff')->first()->id_role;

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
}
