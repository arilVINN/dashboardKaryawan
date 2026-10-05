<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KadivController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'judul'     => 'required|string|max:255',
            'divisi'    => 'required',
            'penerima'  => 'required',
            'deskripsi' => 'nullable|string',
            'tenggat'   => 'required|date',
            'lampiran'  => 'nullable|file|max:5120',
        ]);

        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store('tugas', 'public');
        }

        // Tugas::create($data);

        return back()->with('success', 'Tugas berhasil dikirim');
    }


    private function dataPesan($id)
    {
        $semua = [
            1 => ['id' => 1, 'pengirim' => 'Andi Pratama',        'tanggal' => '05 Oct 2026', 'isi' => 'Izin bertanya pak, mengenai modul laporan bulanan...'],
            2 => ['id' => 2, 'pengirim' => 'Rina Maharani',       'tanggal' => '04 Oct 2026', 'isi' => 'Draft UI/UX dashboard sudah dikirim via Google Drive.'],
            3 => ['id' => 3, 'pengirim' => 'Samuel Sigalingging', 'tanggal' => '01 Oct 2026', 'isi' => 'Dokumentasi REST API sudah diperbarui.'],
        ];

        return $semua[$id] ?? abort(404);
    }

    public function detailPesan($id)
    {
        $pesan = $this->dataPesan($id);

        return view('kadiv.detailPesan', compact('id', 'pesan'));
    }

    public function balasPesan(Request $request, $id)
    {
        $request->validate([
            'isi_balasan' => 'required|string',
            'lampiran'    => 'nullable|file|max:20480',
        ]);

        $balasan = session('balasan_pesan', []);
        $balasan[] = [
            'id_pesan' => (int) $id,
            'isi'      => $request->isi_balasan,
            'file'     => $request->hasFile('lampiran')
                ? $request->file('lampiran')->getClientOriginalName()
                : null,
            'waktu'    => now()->format('d M Y, H.i'),
        ];
        session(['balasan_pesan' => $balasan]);

        if ($request->hasFile('lampiran')) {
            $request->file('lampiran')->store('pesan', 'public');
        }

        return redirect()->route('kadiv.detailPesan', $id)->with('success', 'Balasan terkirim');
    }
}
