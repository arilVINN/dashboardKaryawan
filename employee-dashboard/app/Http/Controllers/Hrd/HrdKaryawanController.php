<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Tugas;
use App\Services\HrdProtectionService;
use Illuminate\Http\Request;

class HrdKaryawanController extends Controller
{
    public function karyawanPage()
    {
        // Rows, filters, sorting, and pagination now live in the
        // App\Livewire\Hrd\KaryawanTable component; the page only supplies
        // the divisi list for the CRUD modals.
        $daftarDivisi = Divisi::orderBy('nama_divisi')->get();

        return view('hrd.daftarKaryawan', compact('daftarDivisi'));
    }

    public function detailPage(string $id)
    {
        $karyawan = Karyawan::with(['divisi', 'user.role', 'tugas', 'lampiran', 'suratIzins' => function ($q) {
            $q->latest();
        }])
            ->where('id_karyawan', $id)
            ->firstOrFail();

        $totalTugas = $karyawan->tugas->count();
        $tugasSelesai = $karyawan->tugas->where('status', Tugas::STATUS_SUDAH_ACC)->count();
        $daftarDivisi = Divisi::orderBy('nama_divisi')->get();

        return view('hrd.detailKaryawan', compact('karyawan', 'totalTugas', 'tugasSelesai', 'daftarDivisi'));
    }

    public function tandaiKeluar(Request $request, string $id)
    {
        $validated = $request->validate([
            'tanggal_keluar' => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        $tanggalKeluar = $validated['tanggal_keluar'] ?? now()->toDateString();

        $karyawan = Karyawan::with('user.role')->where('id_karyawan', $id)->firstOrFail();

        // 1 & 2. Proteksi penonaktifan akun HRD terakhir & penonaktifan akun diri sendiri
        $authUser = $request->user() ?: auth()->user();
        if ($errorMsg = HrdProtectionService::validateDeactivation($authUser, $karyawan)) {
            return back()->with('error', $errorMsg);
        }

        if ($karyawan->tanggal_keluar !== null) {
            return back()->with('error', 'Karyawan ini sudah ditandai keluar.');
        }

        $karyawan->update([
            'tanggal_keluar' => $tanggalKeluar,
            'status_aktif' => 'Tidak Aktif',
            'aktif' => false,
        ]);

        // 3. Catat audit log saat karyawan ditandai keluar (dinonaktifkan)
        AuditLog::catat(
            $authUser?->id_user,
            'nonaktifkan_karyawan',
            'karyawan',
            $karyawan->id_karyawan,
            [
                'nama'           => $karyawan->nama,
                'username'       => $karyawan->user?->username,
                'role'           => $karyawan->user?->role?->nama_role,
                'tanggal_keluar' => $tanggalKeluar,
            ]
        );

        return redirect()
            ->route('hrd.detailKaryawan', $karyawan->id_karyawan)
            ->with('success', 'Karyawan berhasil ditandai keluar. Riwayat tugas tetap tersimpan.');
    }
}
