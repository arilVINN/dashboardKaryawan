<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use App\Models\User2;
use Illuminate\Http\Request;

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

        return response()->json([
            'data' => $notifikasi->map(function ($item) {
                return [
                    'id_notifikasi' => $item->id_notifikasi,
                    'judul_notifikasi' => $item->judul_notifikasi,
                    'isi_notif' => $item->isi_notif,
                    'tanggal_notifikasi' => $item->tanggal_notifikasi,
                ];
            }),
        ]);
    }
}
