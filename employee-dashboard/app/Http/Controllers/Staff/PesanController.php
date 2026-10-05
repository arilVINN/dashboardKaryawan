<?php

namespace App\Http\Controllers\Staff;

use App\Events\PesanDikirim;
use App\Http\Controllers\Controller;
use App\Models\Pesan;
use App\Models\Tugas;
use App\Models\User2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        $validatedThread = $request->validate([
            'tugas_id_tugas' => ['nullable', 'string', 'max:20', 'exists:tugas,id_tugas'],
        ]);

        if (! empty($validatedThread['tugas_id_tugas'])) {
            return $this->showTaskThread($request, $validatedThread['tugas_id_tugas']);
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

        $pesanLangsung = Pesan::query()
            ->with('pengirim.karyawan')
            ->where('penerima_id_user', $user->id_user)
            ->whereNull('tugas_id_tugas')
            ->latest('created_at')
            ->get()
            ->map(fn (Pesan $pesan) => [
                'id_pesan' => $pesan->id_pesan,
                'tipe' => $pesan->tipe ?? 'pesan',
                'arah' => 'masuk',
                'judul_pesan' => $pesan->judul_pesan,
                'deskripsi' => $pesan->deskripsi,
                'tanggal_pesan' => $pesan->tanggal_pesan,
                'pengirim' => $pesan->pengirim ? [
                    'id_user' => $pesan->pengirim->id_user,
                    'username' => $pesan->pengirim->username,
                    'nama' => $pesan->pengirim->karyawan?->nama,
                ] : null,
                'lampiran' => [
                    'link' => $pesan->link_lampiran,
                    'file' => $pesan->file_lampiran
                        ? Storage::disk('public')->url($pesan->file_lampiran)
                        : null,
                ],
                'tugas' => null,
                'created_at' => $pesan->created_at?->toISOString(),
            ]);

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
            'pesan_langsung' => $pesanLangsung,
        ]);
    }

    public function show(Request $request, string $id_pesan)
    {
        $user = $request->user();

        if (! $user instanceof User2) {
            return response()->json([
                'message' => 'User staff belum terautentikasi.',
            ], 401);
        }

        $pesan = Pesan::with([
            'pengirim.karyawan',
            'penerima.karyawan',
            'tugas',
            'balasan.pengirim.karyawan',
            'balasan.penerima.karyawan',
            'balasanDari',
        ])
            ->whereKey($id_pesan)
            ->first();

        $canView = $pesan && (
            $pesan->pengirim_id_user === $user->id_user
            || $pesan->penerima_id_user === $user->id_user
            || $pesan->tugas_karyawan_id_karyawan === $user->karyawan_id_karyawan
        );

        if (! $canView) {
            return response()->json(['message' => 'Pesan tidak ditemukan.'], 404);
        }

        $person = fn (?User2 $account) => $account ? [
            'id_user' => $account->id_user,
            'username' => $account->username,
            'nama' => $account->karyawan?->nama,
        ] : null;
        $root = $pesan->balasanDari ?? $pesan;
        $thread = $root->id_pesan === $pesan->id_pesan ? $pesan : $root;

        return response()->json([
            'data' => [
                'id_pesan' => $pesan->id_pesan,
                'tipe' => $pesan->tipe ?? 'pesan',
                'arah' => $pesan->pengirim_id_user === $user->id_user ? 'keluar' : 'masuk',
                'judul_pesan' => $pesan->judul_pesan,
                'deskripsi' => $pesan->deskripsi,
                'tanggal_pesan' => $pesan->tanggal_pesan,
                'pengirim' => $person($pesan->pengirim),
                'penerima' => $person($pesan->penerima),
                'lampiran' => [
                    'link' => $pesan->link_lampiran,
                    'file' => $pesan->file_lampiran
                        ? Storage::disk('public')->url($pesan->file_lampiran)
                        : null,
                ],
                'tugas' => $pesan->tugas ? [
                    'id_tugas' => $pesan->tugas->id_tugas,
                    'judul_tugas' => $pesan->tugas->judul_tugas,
                ] : null,
                'can_reply' => ($root->tipe ?? 'pesan') === 'pesan',
                'balasan' => $thread->balasan->map(fn (Pesan $reply) => [
                    'id_pesan' => $reply->id_pesan,
                    'balasan_dari_id_pesan' => $reply->balasan_dari_id_pesan,
                    'judul_pesan' => $reply->judul_pesan,
                    'deskripsi' => $reply->deskripsi,
                    'tanggal_pesan' => $reply->tanggal_pesan,
                    'pengirim' => $person($reply->pengirim),
                    'penerima' => $person($reply->penerima),
                    'created_at' => $reply->created_at?->toISOString(),
                ]),
                'created_at' => $pesan->created_at?->toISOString(),
            ],
        ]);
    }

    public function balas(Request $request, string $id_pesan)
    {
        $user = $request->user();

        if (! $user instanceof User2) {
            return response()->json([
                'message' => 'User staff belum terautentikasi.',
            ], 401);
        }

        $validated = $request->validate([
            'deskripsi' => ['required', 'string', 'max:10000'],
        ]);

        $pesan = Pesan::with(['pengirim', 'penerima', 'tugas.karyawan.user.role', 'balasanDari'])
            ->whereKey($id_pesan)
            ->first();

        $canView = $pesan && (
            $user->id_user === $pesan->pengirim_id_user
            || $user->id_user === $pesan->penerima_id_user
            || $user->karyawan_id_karyawan === $pesan->tugas_karyawan_id_karyawan
        );

        if (! $canView) {
            return response()->json(['message' => 'Pesan tidak ditemukan.'], 404);
        }

        $root = $pesan->balasanDari ?? $pesan;
        if (($root->tipe ?? 'pesan') !== 'pesan') {
            return response()->json(['message' => 'Surat tidak dapat dibalas.'], 422);
        }

        $user->loadMissing(['role', 'karyawan.divisi']);
        $penerima = $this->resolveReplyRecipient($pesan, $user);

        if (! $penerima || $penerima->id_user === $user->id_user) {
            return response()->json(['message' => 'Penerima balasan tidak dapat ditentukan.'], 422);
        }

        $balasan = Pesan::create([
            'id_pesan' => $this->generatePesanId(),
            'judul_pesan' => $root->judul_pesan,
            'deskripsi' => $validated['deskripsi'],
            'tipe' => 'pesan',
            'tanggal_pesan' => now()->toDateString(),
            'tugas_id_tugas' => $root->tugas_id_tugas,
            'tugas_karyawan_id_karyawan' => $root->tugas_karyawan_id_karyawan,
            'pengirim_id_user' => $user->id_user,
            'penerima_id_user' => $penerima->id_user,
            'balasan_dari_id_pesan' => $root->id_pesan,
        ]);

        PesanDikirim::dispatch($balasan);

        return response()->json([
            'message' => 'Balasan berhasil dikirim.',
            'data' => [
                'id_pesan' => $balasan->id_pesan,
                'balasan_dari_id_pesan' => $balasan->balasan_dari_id_pesan,
                'tipe' => $balasan->tipe,
                'judul_pesan' => $balasan->judul_pesan,
                'deskripsi' => $balasan->deskripsi,
                'pengirim_id_user' => $balasan->pengirim_id_user,
                'penerima_id_user' => $balasan->penerima_id_user,
            ],
        ], 201);
    }

    private function resolveReplyRecipient(Pesan $pesan, User2 $user): ?User2
    {
        if ($pesan->pengirim_id_user === $user->id_user && $pesan->penerima) {
            return $pesan->penerima;
        }

        if ($pesan->penerima_id_user === $user->id_user && $pesan->pengirim) {
            return $pesan->pengirim;
        }

        $role = strtolower($user->role?->nama_role ?? '');
        if ($role === 'kadiv') {
            return $pesan->tugas?->karyawan?->user;
        }

        if ($role === 'staff' && $user->karyawan?->divisi_id_divisi) {
            return User2::query()
                ->whereHas('role', fn ($query) => $query->whereRaw('LOWER(nama_role) = ?', ['kadiv']))
                ->whereHas('karyawan', fn ($query) =>
                    $query->where('divisi_id_divisi', $user->karyawan->divisi_id_divisi)
                )
                ->first();
        }

        return null;
    }

    private function showTaskThread(Request $request, string $id_tugas)
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

    private function generatePesanId(): string
    {
        do {
            $id = 'PSN'.strtoupper(substr(uniqid(), -8));
        } while (Pesan::where('id_pesan', $id)->exists());

        return $id;
    }
}
