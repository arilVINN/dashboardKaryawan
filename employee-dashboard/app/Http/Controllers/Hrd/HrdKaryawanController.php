<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Karyawan;
use App\Models\Divisi;

class HrdKaryawanController extends Controller
{
    public function karyawanPage(Request $request)
    {
        $karyawan = Karyawan::with(['divisi', 'user.role'])
            ->withCount('tugas')
            ->when($request->divisi, fn ($q, $d) => $q->where('divisi_id_divisi', $d))
            ->when($request->q, fn ($q, $kata) => $q->where('nama', 'like', "%{$kata}%"))
            ->orderBy('nama')
            ->paginate(6)
            ->withQueryString();

        $daftarDivisi = Divisi::orderBy('nama_divisi')->get();

        return view('hrd.daftarKaryawan', compact('karyawan', 'daftarDivisi'));
    }
}