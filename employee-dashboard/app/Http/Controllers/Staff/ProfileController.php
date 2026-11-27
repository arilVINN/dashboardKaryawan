<?php

namespace App\Http\Controllers\Staff;

use App\Actions\StoreKaryawanFoto;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Support\AccountSecurity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load(['karyawan.divisi', 'karyawan.jabatanRef', 'role']);
        $karyawan = $user->karyawan;
        $foto = $karyawan?->foto ?? null;

        $tglMasuk = $karyawan?->tanggal_masuk ?? $karyawan?->tanggal_rekrut;
        $tglAkhirKontrak = $karyawan?->tanggal_akhir_kontrak;
        $tglLahir = $karyawan?->tanggal_lahir;
        $pendidikan = strtoupper(trim((string) $karyawan?->pendidikan));
        $jobLevel = ucfirst(strtolower(trim((string) $karyawan?->job_level)));
        $jabatan = $karyawan ? $karyawan->jabatanLabel() : '-';
        $textOrDash = static fn (?string $value): string => filled(trim((string) $value)) ? trim((string) $value) : '-';

        if ($pendidikan === 'SMA/SMK') {
            $pendidikan = 'SMK';
        }

        return response()->json([
            'message' => 'Data profil',
            'data' => [
                'id_karyawan' => $karyawan?->id_karyawan ?? '-',
                'nama' => $textOrDash($karyawan?->nama ?? $user->username),
                'email' => $textOrDash($karyawan?->email),
                'alamat' => $textOrDash($karyawan?->alamat),
                'jabatan' => $jabatan,
                'jobdesk' => $karyawan?->jobdesk ?: '-',
                'job_level' => $jobLevel !== '' ? $jobLevel : '-',
                'pendidikan' => $pendidikan !== '' ? $pendidikan : '-',
                'status_karyawan' => $karyawan
                    ? ($tglAkhirKontrak ? 'Kontrak' : (filled($karyawan->status_karyawan) ? ucfirst((string) $karyawan->status_karyawan) : 'Tetap'))
                    : '-',
                'status_aktif' => $textOrDash($karyawan?->status_aktif ?? 'Aktif'),
                'divisi' => $textOrDash($karyawan?->divisi?->nama_divisi),
                'no_telepon' => $textOrDash($karyawan?->no_telepon),
                'no_rekening' => $textOrDash($karyawan?->no_rekening),
                'tanggal_masuk' => $tglMasuk ? Carbon::parse($tglMasuk)->format('Y-m-d') : null,
                'tanggal_masuk_label' => $tglMasuk ? Carbon::parse($tglMasuk)->locale('id')->translatedFormat('j M Y') : '-',
                'tanggal_rekrut' => $tglMasuk ? Carbon::parse($tglMasuk)->format('Y-m-d') : null,
                'tanggal_akhir_kontrak' => $tglAkhirKontrak ? Carbon::parse($tglAkhirKontrak)->locale('id')->translatedFormat('j M Y') : null,
                'tanggal_lahir' => $tglLahir ? Carbon::parse($tglLahir)->locale('id')->translatedFormat('j M Y') : '-',
                'jenis_kelamin' => $textOrDash($karyawan?->jenis_kelamin),
                'tingkatan' => $user->role?->nama_role ? ucfirst($user->role->nama_role) : ($jabatan !== '-' ? $jabatan : 'Staff'),
                'tanggal_dibuat' => $user->created_at ? $user->created_at->locale('id')->translatedFormat('j M Y') : '-',
                'foto_url' => $foto ? '/storage/'.$foto : null,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        if (! $user->karyawan) {
            return response()->json(['message' => 'Data karyawan tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('karyawans', 'email')->ignore($user->karyawan->id_karyawan, 'id_karyawan'),
            ],
            'no_telepon' => 'required|string|max:20',
            'alamat' => 'required|string',
            'no_rekening' => 'nullable|string|max:50',
        ]);

        $user->karyawan->update($validated);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
        ]);
    }

    public function updateFoto(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if (! $user->karyawan) {
            return response()->json(['message' => 'Data karyawan tidak ditemukan.'], 404);
        }

        $karyawan = $user->karyawan;

        $path = StoreKaryawanFoto::handle($karyawan, $validated['foto']);

        return response()->json([
            'message' => 'Foto profil berhasil diperbarui.',
            'foto_url' => '/storage/'.$path,
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8',
            'confirm_password' => 'nullable|string|same:new_password',
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 8 karakter.',
            'confirm_password.same' => 'Konfirmasi password tidak cocok.',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['message' => 'Password saat ini salah.'], 400);
        }

        DB::transaction(function () use ($user, $validated): void {
            $user->update([
                'password' => Hash::make($validated['new_password']),
                'must_change_password' => false,
            ]);

            // Rotating the session version and deleting bearer/session rows ends
            // every other active session for this account.
            AccountSecurity::invalidate($user);

            AuditLog::catat(
                $user->id_user,
                'ubah_password',
                'user',
                $user->id_user,
                ['sumber' => 'profil_saya'],
            );
        });

        return response()->json([
            'message' => 'Password berhasil diperbarui.',
        ]);
    }
}
