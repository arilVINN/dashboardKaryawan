<?php

namespace App\Http\Controllers\Kadiv;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Karyawan;

class KadivStaffController extends Controller
{
    /**
     * GET /api/v1/kadiv/staff
     * Mengambil daftar seluruh karyawan di divisi Kadiv.
     */
    public function index(Request $request)
    {
        // Dapatkan user yang sedang login (Kadiv)
        $userKadiv = $request->user();
        
        // Dapatkan divisi dari Kadiv tersebut
        $divisiId = $userKadiv->karyawan->divisi_id_divisi;

        // Ambil semua karyawan yang satu divisi, kecuali Kadiv itu sendiri jika diperlukan,
        // tapi sesuai requirement, ambil semua staff di divisi.
        $staffs = Karyawan::with('user.role')
            ->where('divisi_id_divisi', $divisiId)
            // Opsional: mengecualikan diri sendiri (Kadiv) 
            ->where('id_karyawan', '!=', $userKadiv->karyawan_id_karyawan)
            ->get();

        return response()->json([
            'message' => 'Berhasil mengambil data staff divisi',
            'data' => $staffs
        ]);
    }

    /**
     * GET /api/v1/kadiv/staff/{id}
     * Detail profil staff + daftar tugas yang sedang dikerjakan staff tersebut.
     */
    public function show(Request $request, $id)
    {
        $userKadiv = $request->user();
        $divisiId = $userKadiv->karyawan->divisi_id_divisi;

        // Pastikan staff tersebut ada dan berada di divisi yang sama dengan Kadiv
        $staff = Karyawan::with(['tugas' => function($query) {
            // Urutkan tugas terbaru atau status
            $query->orderBy('tanggal_dibuat', 'desc');
        }])
        ->where('id_karyawan', $id)
        ->where('divisi_id_divisi', $divisiId)
        ->first();

        if (!$staff) {
            return response()->json(['message' => 'Staff tidak ditemukan atau bukan bagian dari divisi Anda'], 404);
        }

        return response()->json([
            'message' => 'Berhasil mengambil detail staff',
            'data' => $staff
        ]);
    }
}
