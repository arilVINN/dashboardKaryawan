<?php

namespace App\Http\Controllers\Hrd;

use App\Actions\StoreKaryawanFoto;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Karyawan;
use App\Models\LampiranKaryawan;
use App\Models\User2;
use App\Models\Divisi;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
        if ($request->has('tanggal_rekrut') && !$request->has('tanggal_masuk')) {
            $request->merge(['tanggal_masuk' => $request->tanggal_rekrut]);
        }

        $request->validate([
            'nama' => 'required|string|max:100',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'tanggal_lahir' => 'nullable|date',
            'tanggal_masuk' => 'nullable|date',
            'tanggal_rekrut' => 'nullable|date',
            'tanggal_akhir_kontrak' => 'nullable|date',
            'no_rekening' => 'nullable|string|max:50',
            'no_telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100|unique:karyawans,email',
            'alamat' => 'nullable|string',
            'jabatan' => ['required', 'string', 'max:100', Rule::in(Karyawan::JABATAN_OPTIONS)],
            'status_aktif' => ['nullable', 'string', Rule::in(Karyawan::STATUS_OPTIONS)],
            'divisi_id_divisi' => 'required|string|max:20',
            // Data akun login
            'username' => 'required|string|max:50|unique:users2,username',
            'password' => 'required|string|min:6',
            'role_id_role' => 'nullable|string|exists:roles,id_role', // Opsional, default staff
            // Lampiran
            'lampiran' => 'nullable|array',
            'lampiran.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'jabatan.in' => 'Jabatan harus salah satu dari: ' . implode(', ', Karyawan::JABATAN_OPTIONS) . '.',
            'lampiran.*.mimes' => 'Format file lampiran harus PDF, JPG, JPEG, atau PNG.',
            'lampiran.*.max' => 'Ukuran file lampiran maksimal 5 MB per file.',
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

            $tanggalMasuk = $request->tanggal_masuk ?? $request->tanggal_rekrut ?? now()->toDateString();

            // 2. Buat Karyawan
            $karyawan = Karyawan::create([
                'id_karyawan' => $newKaryawanId,
                'nama' => $request->nama,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tanggal_lahir' => $request->tanggal_lahir,
                'tanggal_masuk' => $tanggalMasuk,
                'tanggal_akhir_kontrak' => $request->tanggal_akhir_kontrak,
                'no_rekening' => $request->no_rekening,
                'no_telepon' => $request->no_telepon,
                'email' => $request->email,
                'alamat' => $request->alamat,
                'jabatan' => $request->jabatan,
                'status_aktif' => $request->status_aktif ?? 'Aktif',
                'divisi_id_divisi' => $request->divisi_id_divisi,
            ]);

            // Simpan lampiran (jika ada)
            $this->simpanLampiran($request, $karyawan->id_karyawan);

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
        $karyawan = Karyawan::with(['user.role', 'divisi', 'tugas.submitTugas', 'lampiran'])
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

        if ($request->has('tanggal_rekrut') && !$request->has('tanggal_masuk')) {
            $request->merge(['tanggal_masuk' => $request->tanggal_rekrut]);
        }

        $validated = $request->validate([
            'nama' => 'sometimes|required|string|max:100',
            'jenis_kelamin' => 'sometimes|required|in:Laki-laki,Perempuan',
            'tanggal_lahir' => 'sometimes|nullable|date',
            'tanggal_masuk' => 'sometimes|nullable|date',
            'tanggal_rekrut' => 'sometimes|nullable|date',
            'tanggal_akhir_kontrak' => 'sometimes|nullable|date',
            'no_rekening' => 'sometimes|nullable|string|max:50',
            'no_telepon' => 'sometimes|nullable|string|max:20',
            'email' => [
                'sometimes',
                'nullable',
                'email',
                'max:100',
                Rule::unique('karyawans', 'email')->ignore($karyawan->id_karyawan, 'id_karyawan'),
            ],
            'alamat' => 'sometimes|nullable|string',
            'jabatan' => ['sometimes', 'required', 'string', 'max:100', Rule::in(Karyawan::JABATAN_OPTIONS)],
            'status_aktif' => ['sometimes', 'required', 'string', Rule::in(Karyawan::STATUS_OPTIONS)],
            'divisi_id_divisi' => 'sometimes|required|string|max:20|exists:divisis,id_divisi',
            'pendidikan' => 'sometimes|nullable|string|max:50',
            'job_level' => 'sometimes|nullable|string|max:50',
            'status_karyawan' => 'sometimes|nullable|string|max:20',

            'username' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('users2', 'username')->ignore($karyawan->user?->id_user, 'id_user'),
            ],
            'password' => 'sometimes|required|string|min:6',
            'role_id_role' => 'sometimes|required|string|exists:roles,id_role',
            // Lampiran
            'lampiran' => 'sometimes|nullable|array',
            'lampiran.*' => 'sometimes|nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'jabatan.in' => 'Jabatan harus salah satu dari: ' . implode(', ', Karyawan::JABATAN_OPTIONS) . '.',
            'lampiran.*.mimes' => 'Format file lampiran harus PDF, JPG, JPEG, atau PNG.',
            'lampiran.*.max' => 'Ukuran file lampiran maksimal 5 MB per file.',
        ]);

        if (($karyawan->tanggal_keluar !== null || $karyawan->aktif === false || $karyawan->aktif === 0) && ($validated['status_aktif'] ?? null) === 'Aktif') {
            return response()->json([
                'message' => 'Karyawan yang sudah ditandai keluar tidak dapat diaktifkan kembali.',
                'errors' => [
                    'status_aktif' => ['Karyawan yang sudah ditandai keluar tidak dapat diaktifkan kembali.'],
                ],
            ], 422);
        }

        if (isset($validated['role_id_role'])) {
            $role = Role::where('id_role', $validated['role_id_role'])->first();

            if (!in_array(strtolower($role->nama_role), ['kadiv', 'staff', 'hrd'], true)) {
                return response()->json([
                    'message' => 'Role akun hanya boleh kadiv, staff, atau hrd',
                    'errors' => ['role_id_role' => ['Role akun hanya boleh kadiv, staff, atau hrd']],
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

        DB::transaction(function () use ($karyawan, $validated, $request): void {
            $karyawanChanges = array_intersect_key($validated, array_flip([
                'nama',
                'jenis_kelamin',
                'tanggal_lahir',
                'tanggal_masuk',
                'tanggal_rekrut',
                'tanggal_akhir_kontrak',
                'no_rekening',
                'no_telepon',
                'email',
                'alamat',
                'jabatan',
                'status_aktif',
                'divisi_id_divisi',
                'pendidikan',      // <-- Pastikan ini ada
                'job_level',
                'status_karyawan',       // <-- Pastikan ini ada
            ]));

            if (isset($karyawanChanges['tanggal_rekrut']) && !isset($karyawanChanges['tanggal_masuk'])) {
                $karyawanChanges['tanggal_masuk'] = $karyawanChanges['tanggal_rekrut'];
            }
            unset($karyawanChanges['tanggal_rekrut']);

            if (array_key_exists('status_aktif', $karyawanChanges) && $karyawan->tanggal_keluar === null) {
                $isActive = $karyawanChanges['status_aktif'] === 'Aktif';
                $karyawanChanges['aktif'] = $isActive;
            }

            if (!empty($karyawanChanges)) {
                $karyawan->update($karyawanChanges);
            }

            $this->simpanLampiran($request, $karyawan->id_karyawan);

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

        if (! $karyawan->aktif && $request->has('aktif')) {
            return back()->withErrors([
                'aktif' => 'Karyawan ini sudah ditandai keluar dan tidak dapat diaktifkan kembali lewat form edit.',
            ]);
        }

        if (! $karyawan->aktif) {
            unset($data['aktif'], $data['tanggal_keluar']);
        }

        $karyawan->update($data);

        return back()->with('success', 'Data karyawan berhasil diperbarui.');
    }


    public function updateFoto(Request $request, $id)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $karyawan = Karyawan::where('id_karyawan', $id)->first();

        if (! $karyawan) {
            return response()->json(['message' => 'Staff tidak ditemukan'], 404);
        }

        $path = StoreKaryawanFoto::handle($karyawan, $request->file('foto'));

        return response()->json([
            'message' => 'Foto karyawan berhasil diperbarui.',
            'foto_url' => '/storage/' . $path,
        ]);
    }

    public function destroy($id)
    {
        $karyawan = Karyawan::with('user')->where('id_karyawan', $id)->first();

        if (!$karyawan) {
            return response()->json(['message' => 'Staff tidak ditemukan'], 404);
        }

        // Cek apakah ada tugas yang BELUM selesai (bukan sudah di-acc)
        $hasUnfinishedTask = \App\Models\Tugas::where('karyawan_id_karyawan', $karyawan->id_karyawan)
            ->whereNotIn('status', [\App\Models\Tugas::STATUS_SUDAH_ACC])
            ->exists();

        if ($hasUnfinishedTask) {
            return response()->json([
                'message' => 'Karyawan tidak dapat dihapus karena masih memiliki tugas yang belum selesai (belum di-acc).',
            ], 409);
        }

        try {
            DB::transaction(function () use ($karyawan): void {
                // Hapus semua submit_tugas terkait
                \App\Models\Tugas::where('karyawan_id_karyawan', $karyawan->id_karyawan)
                    ->each(function ($tugas): void {
                        $tugas->submitTugas()->delete();
                        $tugas->pesans()->delete();
                        $tugas->delete();
                    });

                // Hapus pesan yang dikirim/diterima user
                if ($karyawan->user) {
                    $karyawan->user->pesanDikirim()->delete();
                    $karyawan->user->pesanDiterima()->delete();
                    $karyawan->user->notifikasis()->delete();
                    $karyawan->user->delete();
                }

                $karyawan->delete();
            });
        } catch (QueryException $e) {
            report($e);
            return response()->json([
                'message' => 'Staff tidak dapat dihapus karena masih memiliki data terkait yang tidak dapat dihapus otomatis.',
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

        if ($normalized === 'hrd manager') {
            return 'hrd';
        }

        if (in_array($normalized, ['staff', 'wakil kepala divisi'], true)) {
            return 'staff';
        }

        return null;
    }

    /**
     * Simpan file lampiran karyawan ke storage folder 'lampiran_karyawan'
     * dan buat baris di tabel lampiran_karyawan.
     */
    private function simpanLampiran(Request $request, string $idKaryawan): void
    {
        $daftarJenis = [
            'lamaran',
            'ijazah',
            'transkrip',
            'ktp',
            'kk',
            'offering_letter',
            'lainnya',
        ];

        foreach ($daftarJenis as $jenis) {
            $file = null;
            if ($request->hasFile("lampiran.{$jenis}")) {
                $file = $request->file("lampiran.{$jenis}");
            } elseif ($request->hasFile("lampiran_{$jenis}")) {
                $file = $request->file("lampiran_{$jenis}");
            } elseif ($request->hasFile($jenis)) {
                $file = $request->file($jenis);
            }

            if ($file) {
                $files = is_array($file) ? $file : [$file];
                foreach ($files as $f) {
                    if ($f && $f->isValid()) {
                        $storedPath = $f->store('lampiran_karyawan', 'public');
                        LampiranKaryawan::create([
                            'id_lampiran' => (string) Str::uuid(),
                            'karyawan_id_karyawan' => $idKaryawan,
                            'jenis_lampiran' => $jenis,
                            'nama_file' => $f->getClientOriginalName(),
                            'path_file' => $storedPath,
                        ]);
                    }
                }
            }
        }
    }
}
