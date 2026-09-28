<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use App\Models\Pesan;
use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user instanceof User2) {
            return response()->json([
                'message' => 'User staff belum terautentikasi.',
            ], 401);
        }

        $karyawanId = $user->karyawan_id_karyawan;

        $tugasQuery = Tugas::where(
            'karyawan_id_karyawan',
            $karyawanId
        );

        $totalTugas = (clone $tugasQuery)->count();

        $tugasBerlangsung = (clone $tugasQuery)
            ->where('status', 'berjalan')
            ->count();

        $tugasPending = (clone $tugasQuery)
            ->whereIn('status', ['baru', 'jeda'])
            ->count();

        $tugasKendala = (clone $tugasQuery)
            ->where('status', 'kendala')
            ->count();

        $tugasSelesai = (clone $tugasQuery)
            ->where('status', 'selesai')
            ->count();

        $completionPercentage = $totalTugas > 0
            ? round(($tugasSelesai / $totalTugas) * 100, 2)
            : 0;

        $pesanTerbaru = Pesan::query()
            ->whereHas('tugas', function ($query) use ($karyawanId) {
                $query->where('karyawan_id_karyawan', $karyawanId);
            })
            ->with(['pengirim', 'tugas'])
            ->latest('created_at')
            ->limit(5)
            ->get();

        $notifikasiTerbaru = Notifikasi::query()
            ->where('user_id_user', $user->id_user)
            ->latest('created_at')
            ->limit(5)
            ->get();

        return response()->json([
            'data' => [
                'statistik' => [
                    'tugas_berlangsung' => $tugasBerlangsung,
                    'tugas_pending' => $tugasPending,
                    'tugas_kendala' => $tugasKendala,
                    'tugas_selesai' => $tugasSelesai,
                    'total_tugas' => $totalTugas,
                    'completion_percentage' => $completionPercentage,
                ],
                'pesan_terbaru' => $pesanTerbaru->map(function (Pesan $pesan) {
                    return [
                        'id_pesan' => $pesan->id_pesan,
                        'deskripsi' => $pesan->deskripsi,
                        'created_at' => $pesan->created_at,
                        'tugas' => $pesan->tugas ? [
                            'id_tugas' => $pesan->tugas->id_tugas,
                            'judul_tugas' => $pesan->tugas->judul_tugas,
                        ] : null,
                        'pengirim' => $pesan->pengirim ? [
                            'id_user' => $pesan->pengirim->id_user,
                            'username' => $pesan->pengirim->username,
                        ] : null,
                    ];
                }),
                'notifikasi_terbaru' => $notifikasiTerbaru->map(function (Notifikasi $notifikasi) {
                    return [
                        'id_notifikasi' => $notifikasi->id_notifikasi,
                        'judul_notifikasi' => $notifikasi->judul_notifikasi,
                        'isi_notif' => $notifikasi->isi_notif,
                        'tanggal_notifikasi' => $notifikasi->tanggal_notifikasi,
                        'created_at' => $notifikasi->created_at,
                    ];
                }),
            ],
        ]);
    }
}
