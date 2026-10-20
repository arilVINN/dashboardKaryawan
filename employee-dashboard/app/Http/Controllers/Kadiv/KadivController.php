<?php

namespace App\Http\Controllers\Kadiv;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Kadiv\KadivTaskController;
use Illuminate\Http\Request;
use App\Models\SuratIzin;

/**
 * Minimal wrapper controller to handle legacy routes that reference KadivController.
 * It forwards the request to the appropriate existing controller methods.
 * This maintains backward compatibility without altering existing business logic.
 */
class KadivController extends Controller
{
    /**
     * Store a new task.
     * Delegates to KadivTaskController::store.
     */
    public function storeSuratIzin(Request $request)
    {
        $data = $request->validate([
            'judul_surat' => 'required|string|max:100',
            'tanggal_izin' => 'required|date',
            'file' => 'nullable|file|max:20480',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        $karyawanId = auth()->user()->karyawan_id_karyawan;

        $lastId = SuratIzin::orderBy('id_surat', 'desc')->first();
        $newId = $lastId
            ? 'SRT' . str_pad(intval(substr($lastId->id_surat, 3)) + 1, 3, '0', STR_PAD_LEFT)
            : 'SRT001';

        SuratIzin::create([
            'id_surat' => $newId,
            'karyawan_id_karyawan' => $karyawanId,
            'judul_surat' => $data['judul_surat'],
            'tanggal_izin' => $data['tanggal_izin'],
            'keterangan' => $data['keterangan'] ?? null,
            'file' => $request->hasFile('file')
                ? $request->file('file')->store('surat_izin', 'public') : null,
        ]);

        return back()->with('success', 'Surat izin berhasil dikirim');
    }

    public function store(Request $request)
    {
        // Resolve the KadivTaskController via the container to honour any dependencies.
        $taskController = app(KadivTaskController::class);
        return $taskController->store($request);
    }
}
