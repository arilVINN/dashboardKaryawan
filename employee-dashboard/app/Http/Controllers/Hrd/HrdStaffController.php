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
use App\Services\HrdProtectionService;
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

        $validated = $request->validate([
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
            'pendidikan' => ['nullable', 'string', Rule::in(Karyawan::PENDIDIKAN_OPTIONS)],
            'job_level' => ['nullable', 'string', Rule::in(Karyawan::JOB_LEVEL_OPTIONS)],
            'divisi_id_divisi' => 'required|string|max:20',
            // Data akun login
            'username' => 'required|string|max:50|unique:users2,username',
            'password' => 'required|string|min:6',
            'role_id_role' => 'nullable|string|exists:roles,id_role', // Opsional, default staff
            // Lampiran
            'lampiran' => 'nullable|array',
            'lampiran.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], $this->validationMessages(), $this->validationAttributes());

        // Try-catch: pastikan divisi ada
        try {
            $divisi = Divisi::where('id_divisi', $request->divisi_id_divisi)->first();

            if (!$divisi) {
                return response()->json([
                    'message' => 'Divisi dengan ID "' . $request->divisi_id_divisi . '" tidak ditemukan. Silakan buat divisi terlebih dahulu.'
                ], 404);
            }

            if (! $divisi->canAcceptMember()) {
                return response()->json([
                    'message' => $this->divisionCapacityMessage($divisi),
                    'errors' => ['divisi_id_divisi' => [$this->divisionCapacityMessage($divisi)]],
                ], 422);
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

            // Akun superadmin tidak boleh dibuat lewat UI/API HRD.
            if (Role::where('id_role', $roleId)->whereRaw('LOWER(nama_role) = ?', ['superadmin'])->exists()) {
                return response()->json([
                    'message' => 'Akun superadmin hanya dapat dibuat melalui perintah artisan.',
                    'errors' => ['role_id_role' => ['Akun superadmin hanya dapat dibuat melalui perintah artisan.']],
                ], 422);
            }

            // Gunakan transaksi agar data karyawan + user konsisten
            DB::beginTransaction();

            // 1. Generate ID Karyawan (KRxxx). Hanya ID berformat KR + angka
            // yang dihitung, sehingga data lama seperti "KRY-..." tidak
            // merusak urutan dan menyebabkan bentrok ID.
            $newKaryawanId = $this->nextGeneratedId(Karyawan::class, 'id_karyawan', 'KR');

            $tanggalMasuk = $validated['tanggal_masuk'] ?? $validated['tanggal_rekrut'] ?? now()->toDateString();
            $tanggalAkhirKontrak = $validated['tanggal_akhir_kontrak'] ?? null;

            // 2. Buat Karyawan
            $karyawan = Karyawan::create([
                'id_karyawan' => $newKaryawanId,
                'nama' => $validated['nama'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                'tanggal_masuk' => $tanggalMasuk,
                'tanggal_akhir_kontrak' => $tanggalAkhirKontrak,
                'no_rekening' => $validated['no_rekening'] ?? null,
                'no_telepon' => $validated['no_telepon'] ?? null,
                'email' => $validated['email'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
                'jabatan' => $validated['jabatan'],
                'status_aktif' => $validated['status_aktif'] ?? 'Aktif',
                'divisi_id_divisi' => $validated['divisi_id_divisi'],
                'pendidikan' => $validated['pendidikan'] ?? null,
                'job_level' => $validated['job_level'] ?? null,
                'status_karyawan' => $tanggalAkhirKontrak ? 'Kontrak' : 'Tetap',
            ]);

            // Simpan lampiran (jika ada)
            $this->simpanLampiran($request, $karyawan->id_karyawan);

            // 3. Generate ID User (USxxx), dengan aturan yang sama seperti
            // ID karyawan agar data lama tidak memicu bentrok.
            $newUserId = $this->nextGeneratedId(User2::class, 'id_user', 'US');

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
        } catch (QueryException $e) {
            DB::rollBack();

            $duplicate = $this->duplicateStaffData($e);

            if ($duplicate !== null) {
                return response()->json($duplicate, 409);
            }

            report($e);

            return response()->json([
                'message' => 'Gagal menambahkan staff karena kendala pada server. Silakan coba lagi.'
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

        // Akun superadmin dikelola lewat CLI, bukan form HRD.
        if (HrdProtectionService::isSuperAdminUser($karyawan->user)) {
            return response()->json([
                'message' => 'Akun superadmin hanya dapat dikelola melalui perintah artisan.',
            ], 403);
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
            'pendidikan' => ['sometimes', 'nullable', 'string', Rule::in(Karyawan::PENDIDIKAN_OPTIONS)],
            'job_level' => ['sometimes', 'nullable', 'string', Rule::in(Karyawan::JOB_LEVEL_OPTIONS)],

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
        ], $this->validationMessages(), $this->validationAttributes());

        // Hanya karyawan yang sudah ditandai keluar (ada tanggal_keluar) yang tidak bisa diaktifkan kembali.
        // Karyawan yang Tidak Aktif karena diset manual tetap bisa diubah kembali menjadi Aktif.
        if ($karyawan->tanggal_keluar !== null && ($validated['status_aktif'] ?? null) === 'Aktif') {
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

        $targetDivisionId = $validated['divisi_id_divisi'] ?? $karyawan->divisi_id_divisi;

        if (array_key_exists('divisi_id_divisi', $validated)
            && $validated['divisi_id_divisi'] !== $karyawan->divisi_id_divisi) {
            $targetDivision = Divisi::where('id_divisi', $targetDivisionId)->first();

            if (! $targetDivision) {
                return response()->json([
                    'message' => 'Divisi tujuan tidak ditemukan.',
                    'errors' => ['divisi_id_divisi' => ['Divisi tujuan tidak ditemukan.']],
                ], 422);
            }

            if (! $targetDivision->canAcceptMember($karyawan->id_karyawan)) {
                return response()->json([
                    'message' => $this->divisionCapacityMessage($targetDivision),
                    'errors' => ['divisi_id_divisi' => [$this->divisionCapacityMessage($targetDivision)]],
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

        // === Proteksi HRD: cegah demosi/deaktivasi HRD terakhir ===
        $authUser = $request->user() ?: auth()->user();

        // 1. Proteksi perubahan status_aktif (deaktivasi)
        if (isset($validated['status_aktif']) && $validated['status_aktif'] !== 'Aktif') {
            if ($errorMsg = HrdProtectionService::validateDeactivation($authUser, $karyawan)) {
                return response()->json([
                    'message' => $errorMsg,
                    'errors'  => ['status_aktif' => [$errorMsg]],
                ], 422);
            }
        }

        // 2. Proteksi perubahan role/jabatan (demosi HRD)
        if (isset($validated['role_id_role']) && $karyawan->user) {
            if ($errorMsg = HrdProtectionService::validateRoleChange($authUser, $karyawan, $validated['role_id_role'])) {
                return response()->json([
                    'message' => $errorMsg,
                    'errors'  => [
                        'jabatan'      => [$errorMsg],
                        'role_id_role' => [$errorMsg],
                    ],
                ], 422);
            }
        }

        try {
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
                ]));

                if (isset($karyawanChanges['tanggal_rekrut']) && !isset($karyawanChanges['tanggal_masuk'])) {
                    $karyawanChanges['tanggal_masuk'] = $karyawanChanges['tanggal_rekrut'];
                }
                unset($karyawanChanges['tanggal_rekrut']);

                $tanggalAkhirKontrak = array_key_exists('tanggal_akhir_kontrak', $karyawanChanges)
                    ? $karyawanChanges['tanggal_akhir_kontrak']
                    : $karyawan->tanggal_akhir_kontrak;
                $karyawanChanges['status_karyawan'] = $tanggalAkhirKontrak ? 'Kontrak' : 'Tetap';

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
        } catch (QueryException $e) {
            $duplicate = $this->duplicateStaffData($e);

            if ($duplicate !== null) {
                return response()->json($duplicate, 409);
            }

            report($e);

            return response()->json([
                'message' => 'Gagal mengubah data staff karena kendala pada server. Silakan coba lagi.',
            ], 500);
        }

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

        if (HrdProtectionService::isSuperAdminUser($karyawan->user)) {
            return response()->json([
                'message' => 'Akun superadmin hanya dapat dikelola melalui perintah artisan.',
            ], 403);
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

        // 1 & 2. Proteksi penghapusan akun HRD & proteksi akun diri sendiri
        $authUser = auth()->user();
        if ($errorMsg = HrdProtectionService::validateDeletion($authUser, $karyawan)) {
            return response()->json(['message' => $errorMsg], 403);
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

    /**
     * Generate the next sequential id (e.g. KR017 / US018).
     *
     * Only ids that match "PREFIX + digits" are considered, so legacy or
     * imported ids such as "KRY-CONTRACT-TEST" cannot reset the sequence and
     * cause a duplicate primary key on every new employee.
     */
    private function nextGeneratedId(string $modelClass, string $column, string $prefix, int $pad = 3): string
    {
        $max = 0;

        $modelClass::query()
            ->where($column, 'like', $prefix.'%')
            ->pluck($column)
            ->each(function ($id) use ($prefix, &$max): void {
                if (preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', (string) $id, $matches) === 1) {
                    $max = max($max, (int) $matches[1]);
                }
            });

        do {
            $max++;
            $candidate = $prefix.str_pad((string) $max, $pad, '0', STR_PAD_LEFT);
        } while ($modelClass::query()->where($column, $candidate)->exists());

        return $candidate;
    }

    /**
     * Translate a unique-constraint violation into a clear, field-specific
     * message. Returns null when the error is not a duplicate-key error.
     */
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

        return [
            'message' => 'ID karyawan atau akun bentrok dengan data lain. Silakan coba lagi.',
        ];
    }

    /**
     * Pesan validasi dalam bahasa Indonesia agar error form mudah dipahami.
     */
    private function validationMessages(): array
    {
        return [
            'nama.required' => 'Nama karyawan wajib diisi.',
            'nama.max' => 'Nama karyawan maksimal 100 karakter.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
            'tanggal_lahir.date' => 'Tanggal lahir tidak valid.',
            'tanggal_masuk.date' => 'Tanggal masuk tidak valid.',
            'tanggal_rekrut.date' => 'Tanggal rekrut tidak valid.',
            'tanggal_akhir_kontrak.date' => 'Tanggal akhir kontrak tidak valid.',
            'no_telepon.max' => 'Nomor telepon maksimal 20 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 100 karakter.',
            'email.unique' => 'Email sudah digunakan karyawan lain. Gunakan email lain atau kosongkan.',
            'jabatan.required' => 'Jabatan wajib dipilih.',
            'jabatan.in' => 'Jabatan harus salah satu dari: '.implode(', ', Karyawan::JABATAN_OPTIONS).'.',
            'jabatan.max' => 'Jabatan maksimal 100 karakter.',
            'status_aktif.in' => 'Status harus salah satu dari: '.implode(', ', Karyawan::STATUS_OPTIONS).'.',
            'pendidikan.in' => 'Pendidikan harus salah satu dari: '.implode(', ', Karyawan::PENDIDIKAN_OPTIONS).'.',
            'job_level.in' => 'Job level harus salah satu dari: '.implode(', ', Karyawan::JOB_LEVEL_OPTIONS).'.',
            'divisi_id_divisi.required' => 'Divisi wajib dipilih.',
            'divisi_id_divisi.max' => 'Divisi tidak valid.',
            'username.required' => 'Username wajib diisi.',
            'username.max' => 'Username maksimal 50 karakter.',
            'username.unique' => 'Username sudah digunakan. Silakan pilih username lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'lampiran.*.file' => 'Lampiran gagal diunggah. Silakan pilih file lain.',
            'lampiran.*.mimes' => 'Format file lampiran harus PDF, JPG, JPEG, atau PNG.',
            'lampiran.*.max' => 'Ukuran file lampiran maksimal 5 MB per file.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'nama' => 'nama karyawan',
            'jenis_kelamin' => 'jenis kelamin',
            'tanggal_lahir' => 'tanggal lahir',
            'tanggal_masuk' => 'tanggal masuk',
            'tanggal_rekrut' => 'tanggal rekrut',
            'tanggal_akhir_kontrak' => 'tanggal akhir kontrak',
            'no_rekening' => 'no. rekening',
            'no_telepon' => 'nomor telepon',
            'divisi_id_divisi' => 'divisi',
            'job_level' => 'job level',
        ];
    }

    private function roleNameForJabatan(string $jabatan): ?string
    {
        $normalized = mb_strtolower(trim($jabatan));

        if (preg_match('/^(?:kepala\s+divisi|direktur\s+utama)(?:\s|$)/i', $normalized) === 1) {
            return 'kadiv';
        }

        if ($normalized === 'hrd manager') {
            return 'hrd';
        }

        if (in_array($normalized, ['staff', 'wakil kepala divisi', 'markom', 'marcom', 'layouter'], true)
            || preg_match('/^(?:markom|marcom)(\s|$)/i', $normalized) === 1) {
            return 'staff';
        }

        return null;
    }

    private function divisionCapacityMessage(Divisi $divisi): string
    {
        if ($divisi->isGrouping()) {
            return 'Manajemen adalah level grup dan tidak memiliki anggota langsung.';
        }

        if ($divisi->isSinglePerson()) {
            return 'Unit '.$divisi->nama_divisi.' hanya dapat diisi oleh satu orang.';
        }

        return 'Kapasitas unit '.$divisi->nama_divisi.' sudah penuh.';
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
