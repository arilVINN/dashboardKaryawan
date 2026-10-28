<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\CalendarNote;
use App\Models\Notifikasi;
use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user instanceof User2) {
            return response()->json([
                'message' => 'User staff belum terautentikasi.',
            ], 401);
        }

        $query = Notifikasi::query()
            ->where('user_id_user', $user->id_user)
            ->orderBy('created_at', 'desc');

        $notifikasi = $query->get();

        // Ambil catatan kalender yang jatuh besok (pengingat sehari sebelum)
        $besok = Carbon::tomorrow()->toDateString();
        $tanggalLabel = Carbon::parse($besok)->locale('id')->isoFormat('dddd, D MMMM Y');

        $notesBesok = CalendarNote::query()
            ->where('user_id_user', $user->id_user)
            ->whereDate('tanggal', $besok)
            ->get();

        $pengingatNote = $notesBesok->map(function (CalendarNote $note) use ($tanggalLabel) {
            return [
                'id_notifikasi'      => 'note-reminder-' . $note->id,
                'judul_notifikasi'   => 'Pengingat Kalender',
                'isi_notif'          => "Catatan \"{$note->judul}\" dijadwalkan besok, {$tanggalLabel}.",
                'tanggal_notifikasi' => now()->toDateString(),
                'created_at'         => now()->toISOString(),
                'is_note_reminder'   => true,
            ];
        })->values()->all();

        // Ambil tugas staf yang tenggat waktunya (deadline) besok
        $karyawanId = $user->karyawan_id_karyawan;
        $tugasBesok = $karyawanId ? Tugas::query()
            ->where('karyawan_id_karyawan', $karyawanId)
            ->where('status', '!=', Tugas::STATUS_SUDAH_ACC)
            ->whereDate('deadline', $besok)
            ->get() : collect();

        $pengingatTugas = $tugasBesok->map(function (Tugas $tugas) use ($tanggalLabel) {
            $waktuDeadline = Carbon::parse($tugas->deadline);
            $jam = $waktuDeadline->format('H.i');
            $waktuLabel = $jam !== '00.00' ? " pukul {$jam}" : '';

            return [
                'id_notifikasi'      => 'tugas-reminder-' . $tugas->id_tugas,
                'judul_notifikasi'   => 'Pengingat Tugas',
                'isi_notif'          => "Tugas \"{$tugas->judul_tugas}\" tenggat waktunya besok, {$tanggalLabel}{$waktuLabel}.",
                'tanggal_notifikasi' => now()->toDateString(),
                'created_at'         => now()->toISOString(),
                'is_tugas_reminder'  => true,
            ];
        })->values()->all();

        $notifikasiData = array_merge(
            $pengingatNote,
            $pengingatTugas,
            $notifikasi->map(function ($item) {
                return [
                    'id_notifikasi'      => $item->id_notifikasi,
                    'judul_notifikasi'   => $item->judul_notifikasi,
                    'isi_notif'          => $item->isi_notif,
                    'tanggal_notifikasi' => $item->tanggal_notifikasi,
                    'created_at'         => $item->created_at,
                ];
            })->all()
        );

        return response()->json([
            'data' => $notifikasiData,
        ]);
    }
}
