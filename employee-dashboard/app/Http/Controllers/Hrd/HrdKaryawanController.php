<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Tugas;

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
        $karyawan = Karyawan::with(['divisi', 'user.role', 'tugas'])
            ->where('id_karyawan', $id)
            ->firstOrFail();

        $totalTugas = $karyawan->tugas->count();
        $tugasSelesai = $karyawan->tugas->where('status', Tugas::STATUS_SUDAH_ACC)->count();

        return view('hrd.detailKaryawan', compact('karyawan', 'totalTugas', 'tugasSelesai'));
    }
}
