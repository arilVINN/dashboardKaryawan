<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Tugas;
use App\Queries\HrdKaryawanQuery;
use Illuminate\Http\Request;

class HrdKaryawanController extends Controller
{
    public function karyawanPage(Request $request)
    {
        $karyawan = (new HrdKaryawanQuery)
            ->apply($request->only(['divisi', 'jabatan', 'status', 'q', 'sort', 'dir']))
            ->paginate(6)
            ->withQueryString();

        $daftarDivisi = Divisi::orderBy('nama_divisi')->get();
        $daftarJabatan = Karyawan::query()
            ->whereNotNull('jabatan')
            ->distinct()
            ->orderBy('jabatan')
            ->pluck('jabatan');

        return view('hrd.daftarKaryawan', compact('karyawan', 'daftarDivisi', 'daftarJabatan'));
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
