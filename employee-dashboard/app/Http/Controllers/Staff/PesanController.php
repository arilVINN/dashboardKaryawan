<?php

namespace App\Http\Controllers\Staff;

use App\Events\PesanDikirim;
use App\Http\Controllers\Controller;
use App\Models\Pesan;
use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Http\Request;

class PesanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user instanceof User2) {
            return response()->json([
                'message' => 'User staff belum terautentikasi.',
            ], 401);
        }

        $query = Tugas::query()
            ->with([
                'latestPesan.pengirim',
            ])
            ->withCount('pesans')
            ->where('karyawan_id_karyawan', $user->karyawan_id_karyawan);

        $tugas = $query
            ->orderBy('tanggal_update', 'desc')
            ->orderBy('tanggal_dibuat', 'desc')
            ->get();

        return response()->json([
            'data' => $tugas->map(function ($item) {
                return [
                    'id_tugas' => $item->id_tugas,
                    'judul_tugas' => $item->judul_tugas,
                    'status' => $item->status,
                    'progress' => $item->progress,
                    'deadline' => $item->deadline,

                    'jumlah_pesan' => $item->pesans_count,

                    'pesan_terakhir' => $item->latestPesan ? [
                        'id_pesan' => $item->latestPesan->id_pesan,
                        'deskripsi' => $item->latestPesan->deskripsi,
                        'tanggal_pesan' => $item->latestPesan->tanggal_pesan,

                        'pengirim' => $item->latestPesan->pengirim ? [
                            'id_user' => $item->latestPesan->pengirim->id_user,
                            'username' => $item->latestPesan->pengirim->username,
                        ] : null,
                    ] : null,
                ];
            }),
        ]);
    }

    public function show(Request $request, string $id_tugas)
    {
        $user = $request->user();

        if (! $user instanceof User2) {
            return response()->json([
                'message' => 'User staff belum terautentikasi.',
            ], 401);
        }

        $tugas = Tugas::with([
            'pesans' => function ($query) {
                $query->with('pengirim')
                    ->orderBy('tanggal_pesan', 'asc')
                    ->orderBy('created_at', 'asc');
            },
        ])
            ->where('karyawan_id_karyawan', $user->karyawan_id_karyawan)
            ->find($id_tugas);

        if (! $tugas) {
            return response()->json([
                'message' => 'Tugas tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'data' => [
                'tugas' => [
                    'id_tugas' => $tugas->id_tugas,
                    'judul_tugas' => $tugas->judul_tugas,
                    'deskripsi' => $tugas->deskripsi,
                    'deadline' => $tugas->deadline,
                    'progress' => $tugas->progress,
                    'status' => $tugas->status,
                    'tanggal_dibuat' => $tugas->tanggal_dibuat,
                    'tanggal_update' => $tugas->tanggal_update,
                ],

                'pesan' => $tugas->pesans->map(function ($pesan) {
                    return [
                        'id_pesan' => $pesan->id_pesan,
                        'judul_pesan' => $pesan->judul_pesan,
                        'deskripsi' => $pesan->deskripsi,
                        'tanggal_pesan' => $pesan->tanggal_pesan,

                        'pengirim' => $pesan->pengirim ? [
                            'id_user' => $pesan->pengirim->id_user,
                            'username' => $pesan->pengirim->username,
                        ] : null,
                    ];
                }),
            ],
        ]);
    }

    public function send(Request $request)
    {
        $user = $request->user();

        if (! $user instanceof User2) {
            return response()->json([
                'message' => 'User staff belum terautentikasi.',
            ], 401);
        }

        $validated = $request->validate([
            'tugas_id_tugas' => [
                'required',
                'string',
                'exists:tugas,id_tugas',
            ],
            'judul_pesan' => [
                'required',
                'string',
                'max:200',
            ],
            'deskripsi' => [
                'required',
                'string',
            ],
        ]);

        $tugas = Tugas::query()
            ->where('karyawan_id_karyawan', $user->karyawan_id_karyawan)
            ->find($validated['tugas_id_tugas']);

        if (! $tugas) {
            return response()->json([
                'message' => 'Tugas tidak ditemukan.',
            ], 404);
        }

        $pesan = Pesan::create([
            'id_pesan' => $this->generatePesanId(),
            'judul_pesan' => $validated['judul_pesan'],
            'deskripsi' => $validated['deskripsi'],
            'tanggal_pesan' => now()->toDateString(),
            'tugas_id_tugas' => $tugas->id_tugas,
            'tugas_karyawan_id_karyawan' => $tugas->karyawan_id_karyawan,
            'pengirim_id_user' => $user->id_user,
        ]);

        PesanDikirim::dispatch($pesan);
        $pesan->load('pengirim');

        return response()->json([
            'message' => 'Pesan berhasil dikirim.',
            'data' => [
                'id_pesan' => $pesan->id_pesan,
                'judul_pesan' => $pesan->judul_pesan,
                'deskripsi' => $pesan->deskripsi,
                'tanggal_pesan' => $pesan->tanggal_pesan,
                'tugas_id_tugas' => $pesan->tugas_id_tugas,

                'pengirim' => [
                    'id_user' => $pesan->pengirim->id_user,
                    'username' => $pesan->pengirim->username,
                ],
            ],
        ], 201);
    }

    private function generatePesanId(): string
    {
        do {
            $id = 'PSN'.strtoupper(substr(uniqid(), -8));
        } while (Pesan::where('id_pesan', $id)->exists());

        return $id;
    }
}
