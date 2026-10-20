<?php

namespace App\Http\Controllers\Staff;
// app/Http/Controllers/Staff/SuratIzinController.php
use App\Http\Controllers\Controller;
use App\Models\SuratIzin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SuratIzinController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'judul_surat' => 'required|string|max:100',
            'tanggal_izin' => 'required|date',
            'file' => 'nullable|file|max:20480',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        $karyawanId = auth()->user()->karyawan_id_karyawan;

        do {
            $newId = 'SRT' . strtoupper(Str::random(3));
        } while (SuratIzin::whereKey($newId)->exists());

        SuratIzin::create([
            'id_surat' => $newId,
            'karyawan_id_karyawan' => $karyawanId,
            'judul_surat' => $data['judul_surat'],
            'tanggal_izin' => $data['tanggal_izin'],
            'keterangan' => $data['keterangan'] ?? null,
            'file' => $request->hasFile('file')
                ? $request->file('file')->store('surat_izin', 'public') : null,
        ]);

        return back()->with('success', 'Surat izin berhasil dikirim.');
    }
}
