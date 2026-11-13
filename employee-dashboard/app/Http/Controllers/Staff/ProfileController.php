<?php

namespace App\Http\Controllers\Staff;

use App\Actions\StoreKaryawanFoto;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load(['karyawan.divisi', 'role']);
        $karyawan = $user->karyawan;
        $foto = $karyawan->foto ?? null;

        $tglMasuk = $karyawan?->tanggal_masuk ?? $karyawan?->tanggal_rekrut;
        $tglAkhirKontrak = $karyawan?->tanggal_akhir_kontrak;
        $tglLahir = $karyawan?->tanggal_lahir;
        $pendidikan = strtoupper(trim((string) $karyawan?->pendidikan));
        $jobLevel = ucfirst(strtolower(trim((string) $karyawan?->job_level)));

        if ($pendidikan === 'SMA/SMK') {
            $pendidikan = 'SMK';
        }

        return response()->json([
            'message' => 'Data profil',
            'data' => [
                'id_karyawan' => $karyawan?->id_karyawan ?? '-',
                'nama' => $karyawan?->nama ?? $user->username,
                'email' => $karyawan?->email ?? '-',
                'alamat' => $karyawan?->alamat ?? '-',
                'jabatan' => $karyawan?->jabatan ?? '-',
                'job_level' => $jobLevel !== '' ? $jobLevel : '-',
                'pendidikan' => $pendidikan !== '' ? $pendidikan : '-',
                'status_karyawan' => $karyawan ? ($tglAkhirKontrak ? 'Kontrak' : 'Tetap') : '-',
                'status_aktif' => $karyawan?->status_aktif ?? 'Aktif',
                'divisi' => $karyawan?->divisi->nama_divisi ?? '-',
                'no_telepon' => $karyawan?->no_telepon ?? '-',
                'no_rekening' => $karyawan?->no_rekening ?? '-',
                'tanggal_masuk' => $tglMasuk ? \Carbon\Carbon::parse($tglMasuk)->format('Y-m-d') : null,
                'tanggal_masuk_label' => $tglMasuk ? \Carbon\Carbon::parse($tglMasuk)->format('d M Y') : '-',
                'tanggal_rekrut' => $tglMasuk ? \Carbon\Carbon::parse($tglMasuk)->format('Y-m-d') : null,
                'tanggal_akhir_kontrak' => $tglAkhirKontrak ? \Carbon\Carbon::parse($tglAkhirKontrak)->format('d M Y') : 'Karyawan Tetap / Tidak Ada',
                'tanggal_lahir' => $tglLahir ? \Carbon\Carbon::parse($tglLahir)->format('d M Y') : '-',
                'jenis_kelamin' => $karyawan?->jenis_kelamin ?? '-',
                'tingkatan' => $user->role?->nama_role ? ucfirst($user->role->nama_role) : ($karyawan?->jabatan ?? 'Staff'),
                'tanggal_dibuat' => $user->created_at ? $user->created_at->format('d F Y') : '-',
                'foto_url' => $foto ? '/storage/'.$foto : null,
            ]
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
        
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Password saat ini salah.'], 400);
        }

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->new_password)
        ]);

        return response()->json([
            'message' => 'Password berhasil diperbarui.',
        ]);
    }
}
