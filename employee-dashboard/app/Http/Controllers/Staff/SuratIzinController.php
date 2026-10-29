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
        // 1. Sesuaikan validasi dengan input pada modal baru
        $data = $request->validate([
            'jenis_izin'      => 'required|string|max:100',
            'judul_surat'     => 'required|string|max:100',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'file'            => 'nullable|file|max:20480',
            'keterangan'      => 'nullable|string|max:1000',
        ]);

        $karyawanId = auth()->user()->karyawan_id_karyawan;

        do {
            $newId = 'SRT' . strtoupper(Str::random(3));
        } while (SuratIzin::whereKey($newId)->exists());

        // 2. Masukkan field baru ke dalam proses create database
        SuratIzin::create([
            'id_surat'             => $newId,
            'karyawan_id_karyawan' => $karyawanId,
            'jenis_izin'           => $data['jenis_izin'],
            'judul_surat'          => $data['judul_surat'],
            'tanggal_izin'         => $data['tanggal_mulai'], // <-- Tambahkan ini agar tidak error NOT NULL
            'tanggal_mulai'        => $data['tanggal_mulai'],
            'tanggal_selesai'      => $data['tanggal_selesai'],
            'keterangan'           => $data['keterangan'] ?? null,
            'file'                 => $request->hasFile('file')
                ? $request->file('file')->store('surat_izin', 'public') : null,
            'status'               => 'Pending',
        ]);

        return back()->with('success', 'Surat izin berhasil dikirim.');
    }
}
