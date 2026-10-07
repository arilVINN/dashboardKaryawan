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
        $sort = $request->input('sort', 'nama');
        $dir = $request->input('dir') === 'desc' ? 'desc' : 'asc';

        // Kolom yang boleh dipakai untuk ORDER BY (whitelist, anti SQL injection).
        $sortColumns = [
            'id' => 'id_karyawan',
            'nama' => 'nama',
            'jabatan' => 'jabatan',
        ];

        $karyawan = Karyawan::with(['divisi', 'user.role'])
            ->withCount('tugas')
            ->when($request->filled('divisi'), fn ($q) => $q->where('divisi_id_divisi', $request->divisi))
            ->when($request->filled('jabatan'), fn ($q) => $q->where('jabatan', $request->jabatan))
            ->when($request->filled('q'), fn ($q) => $q->where('nama', 'like', '%' . $request->q . '%'))
            ->when($request->input('status') === 'aktif', fn ($q) => $q->whereHas('user'))
            ->when($request->input('status') === 'belum', fn ($q) => $q->whereDoesntHave('user'));

        if ($sort === 'divisi') {
            $karyawan->orderByRaw(
                "(select nama_divisi from divisis where divisis.id_divisi = karyawans.divisi_id_divisi) {$dir}"
            )->orderBy('id_karyawan');
        } else {
            $karyawan->orderBy($sortColumns[$sort] ?? 'nama', $dir);
        }

        $karyawan = $karyawan->paginate(6)->withQueryString();

        $daftarDivisi = Divisi::orderBy('nama_divisi')->get();
        $daftarJabatan = Karyawan::query()
            ->whereNotNull('jabatan')
            ->distinct()
            ->orderBy('jabatan')
            ->pluck('jabatan');

        return view('hrd.daftarKaryawan', compact('karyawan', 'daftarDivisi', 'daftarJabatan'));
    }
}