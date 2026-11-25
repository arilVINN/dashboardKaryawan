<?php

namespace App\Http\Controllers\Kadiv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Karyawan;

class KadivStaffController extends Controller
{
    /**
    * GET /api/kadiv/staff
     * Mengambil daftar seluruh karyawan di divisi Kadiv.
     */
    public function index(Request $request)
    {
        $userKadiv = $request->user();
        $isDirektur = $userKadiv->isDirektur();
        $divisiId = $request->query('divisi_id') ?? ($isDirektur ? null : $userKadiv->karyawan?->divisi_id_divisi);

        $query = Karyawan::with(['user.role', 'divisi', 'jabatanRef'])
            ->whereHas('user.role', fn ($query) => $query->whereRaw('LOWER(nama_role) = ?', ['staff']));

        if ($divisiId) {
            $query->where('divisi_id_divisi', $divisiId);
        }

        $staffs = $query->get();

        return response()->json([
            'message' => 'Berhasil mengambil data staff divisi',
            'data' => $staffs
        ]);
    }

    /**
    * GET /api/kadiv/staff/{id}
     * Detail profil staff + daftar tugas yang sedang dikerjakan staff tersebut.
     */
    public function show(Request $request, $id)
    {
        $userKadiv = $request->user();

        $query = Karyawan::with(['user.role', 'divisi', 'jabatanRef', 'tugas' => function($query) {
            $query->orderBy('tanggal_dibuat', 'desc');
        }])
        ->where('id_karyawan', $id)
        ->whereHas('user.role', fn ($query) => $query->whereRaw('LOWER(nama_role) = ?', ['staff']));

        if (! $userKadiv->isDirektur()) {
            $query->where('divisi_id_divisi', $userKadiv->karyawan?->divisi_id_divisi);
        }

        $staff = $query->first();

        if (!$staff) {
            return response()->json(['message' => 'Staff tidak ditemukan atau bukan bagian dari divisi Anda'], 404);
        }

        return response()->json([
            'message' => 'Berhasil mengambil detail staff',
            'data' => $staff
        ]);
    }
}
