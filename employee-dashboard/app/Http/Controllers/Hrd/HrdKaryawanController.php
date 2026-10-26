<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Tugas;
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
        $karyawan = Karyawan::with(['divisi', 'user.role', 'tugas', 'lampiran'])
            ->where('id_karyawan', $id)
            ->firstOrFail();

        $totalTugas = $karyawan->tugas->count();
        $tugasSelesai = $karyawan->tugas->where('status', Tugas::STATUS_SUDAH_ACC)->count();

        return view('hrd.detailKaryawan', compact('karyawan', 'totalTugas', 'tugasSelesai'));
    }

    public function tandaiKeluar(Request $request, string $id)
    {
        $validated = $request->validate([
            'tanggal_keluar' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $karyawan = Karyawan::where('id_karyawan', $id)->firstOrFail();

        if ($karyawan->tanggal_keluar !== null) {
            return back()->with('error', 'Karyawan ini sudah ditandai keluar.');
        }

        $karyawan->update([
            'tanggal_keluar' => $validated['tanggal_keluar'],
            'status_aktif' => 'Tidak Aktif',
            'aktif' => false,
        ]);

        return redirect()
            ->route('hrd.detailKaryawan', $karyawan->id_karyawan)
            ->with('success', 'Karyawan berhasil ditandai keluar. Riwayat tugas tetap tersimpan.');
    }
}
