<?php

namespace App\Http\Controllers\Kadiv;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use Illuminate\Http\Request;

class KadivStaffController extends Controller
{
    /**
     * Fields a Kadiv may see about a staff member. Payroll/bank details
     * (`no_rekening`), home address, birth date, and education are excluded
     * because KaryawanPolicy::viewSensitive reserves them for HRD/admin.
     */
    private const STAFF_FIELDS = [
        'id_karyawan',
        'nama',
        'jenis_kelamin',
        'jabatan',
        'divisi_id_divisi',
        'foto',
        'status_aktif',
        'email',
        'no_telepon',
    ];

    /**
     * GET /api/kadiv/staff
     * Mengambil daftar seluruh karyawan di divisi Kadiv.
     */
    public function index(Request $request)
    {
        $userKadiv = $request->user();
        $isExecutive = $userKadiv->isDirektur();

        // A Kadiv is always scoped to their own division, regardless of any
        // client-supplied `divisi_id`. Only director-level monitors may ask
        // for another division (or all divisions).
        $divisiId = $isExecutive
            ? ($request->filled('divisi_id') ? (string) $request->query('divisi_id') : null)
            : $userKadiv->karyawan?->divisi_id_divisi;

        if (! $isExecutive && ! $divisiId) {
            return response()->json([
                'message' => 'Divisi Anda tidak ditemukan.',
            ], 403);
        }

        $query = Karyawan::query()
            ->select(self::STAFF_FIELDS)
            ->with([
                'user:id_user,username,karyawan_id_karyawan,role_id_role',
                'user.role:id_role,nama_role',
            ])
            ->whereHas('user.role', fn ($query) => $query->whereRaw('LOWER(nama_role) = ?', ['staff']));

        if ($divisiId) {
            $query->where('divisi_id_divisi', $divisiId);
        }

        $staffs = $query->get();

        return response()->json([
            'message' => 'Berhasil mengambil data staff divisi',
            'data' => $staffs,
        ]);
    }

    /**
     * GET /api/kadiv/staff/{id}
     * Detail profil staff + daftar tugas yang sedang dikerjakan staff tersebut.
     */
    public function show(Request $request, $id)
    {
        $userKadiv = $request->user();

        $query = Karyawan::query()
            ->select(self::STAFF_FIELDS)
            ->with([
                'user:id_user,username,karyawan_id_karyawan,role_id_role',
                'user.role:id_role,nama_role',
                'tugas' => function ($query) {
                    $query->orderBy('tanggal_dibuat', 'desc');
                },
            ])
            ->where('id_karyawan', $id)
            ->whereHas('user.role', fn ($query) => $query->whereRaw('LOWER(nama_role) = ?', ['staff']));

        if (! $userKadiv->isDirektur()) {
            $query->where('divisi_id_divisi', $userKadiv->karyawan?->divisi_id_divisi);
        }

        $staff = $query->first();

        if (! $staff) {
            return response()->json(['message' => 'Staff tidak ditemukan atau bukan bagian dari divisi Anda'], 404);
        }

        return response()->json([
            'message' => 'Berhasil mengambil detail staff',
            'data' => $staff,
        ]);
    }
}
