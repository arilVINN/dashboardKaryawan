<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use App\Events\PesanDikirim;
use App\Models\Pesan;
use App\Models\User2;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HrdPesanController extends Controller
{
    public function page(Request $request)
    {
        $user = $request->user();
        $pesans = $this->allMessages();

        return view('hrd.pesan', [
            'pesans' => $pesans,
            'totalPesan' => $pesans->count(),
            'pesanMasuk' => $pesans->where('penerima_id_user', $user->id_user)->count(),
            'pesanKeluar' => $pesans->where('pengirim_id_user', $user->id_user)->count(),
        ]);
    }

    public function daftarPage(Request $request)
    {
        $pesans = $this->allMessages();

        return view('hrd.daftarPesan', compact('pesans'));
    }

    private function allMessages()
    {
        return Pesan::with(['pengirim.karyawan', 'penerima.karyawan'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function detailPage(Request $request, string $id_pesan)
    {
        $user = $request->user();
        $pesan = Pesan::with([
            'pengirim.karyawan',
            'penerima.karyawan',
            'tugas',
            'balasan.pengirim.karyawan',
            'balasan.penerima.karyawan',
            'balasanDari',
        ])
            ->whereKey($id_pesan)
            ->firstOrFail();

        $root = $pesan->balasanDari ?? $pesan;
        $thread = $root->balasan->prepend($root)->sortBy('created_at');
        $isParticipant = $pesan->pengirim_id_user === $user->id_user
            || $pesan->penerima_id_user === $user->id_user;

        return view('hrd.detailPesan', [
            'pesan' => $pesan,
            'thread' => $thread,
            'canReply' => $isParticipant && ($root->tipe ?? 'pesan') === 'pesan',
        ]);
    }

    /**
     * GET /api/hrd/pesan
     * Pusat pesan global & status metrics (Total, Belum Dibaca, Selesai).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Pesan::with(['pengirim.karyawan', 'penerima.karyawan'])
            ->orderBy('created_at', 'desc');

        $pesan = $query->get();

        $metrics = [
            'total' => $pesan->count(),
            'belum_dibaca' => $pesan->where('status', 'belum_dibaca')->count(),
            'selesai' => $pesan->where('status', 'dibaca')->count(),
        ];

        return response()->json([
            'message' => 'Berhasil mengambil pusat pesan',
            'data' => [
                'metrics' => $metrics,
                'list_pesan' => $pesan
            ]
        ]);
    }

    /**
     * POST /api/hrd/pesan
     * Tulis surat/pesan baru dengan lampiran file/link.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'penerima_id_user' => 'required|exists:users2,id_user',
            'judul_pesan' => 'required|string|max:200',
            'deskripsi' => 'required|string',
            'tipe' => 'required|in:pesan,surat',
            'file_lampiran' => 'nullable|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
            'link_lampiran' => 'nullable|url|max:2048'
        ]);

        $filePath = null;
        if ($request->hasFile('file_lampiran')) {
            $filePath = $request->file('file_lampiran')->store('lampiran_pesan', 'public');
        }

        $pesan = Pesan::create([
            'id_pesan' => $this->generateMessageId(),
            'judul_pesan' => strip_tags($validated['judul_pesan']),
            'deskripsi' => $validated['deskripsi'],
            'tipe' => $validated['tipe'],
            'tanggal_pesan' => now()->toDateString(),
            'pengirim_id_user' => $user->id_user,
            'penerima_id_user' => $validated['penerima_id_user'],
            'status' => 'belum_dibaca',
            'file_lampiran' => $filePath,
            'link_lampiran' => $validated['link_lampiran'] ?? null,
        ]);

        PesanDikirim::dispatch($pesan);

        return response()->json([
            'message' => 'Pesan/Surat berhasil dikirim',
            'data' => $pesan
        ], 201);
    }
    public function show(Request $request, string $id_pesan): JsonResponse
    {
        $user = $request->user();
        $message = Pesan::query()
            ->with(['pengirim.karyawan', 'penerima.karyawan', 'tugas', 'balasanDari'])
            ->whereKey($id_pesan)
            ->first();

        if (! $user instanceof User2 || ! $message) {
            return response()->json(['message' => 'Pesan tidak ditemukan.'], 404);
        }

        $root = $message->balasanDari ?? $message;
        $isParticipant = $message->pengirim_id_user === $user->id_user
            || $message->penerima_id_user === $user->id_user;
        $thread = $root->id_pesan === $message->id_pesan ? $message : $root;
        $replies = $thread->balasan()
            ->with(['pengirim.karyawan', 'penerima.karyawan'])
            ->orderBy('created_at')
            ->orderBy('id_pesan')
            ->get();

        $person = fn (?User2 $account) => $account ? [
            'id_user' => $account->id_user,
            'username' => $account->username,
            'nama' => $account->karyawan?->nama,
        ] : null;

        return response()->json([
            'data' => [
                'id_pesan' => $message->id_pesan,
                'tipe' => $message->tipe ?? 'pesan',
                'arah' => $message->pengirim_id_user === $user->id_user ? 'keluar' : 'masuk',
                'judul_pesan' => $message->judul_pesan,
                'deskripsi' => $message->deskripsi,
                'tanggal_pesan' => $message->tanggal_pesan,
                'pengirim' => $person($message->pengirim),
                'penerima' => $person($message->penerima),
                'lampiran' => [
                    'link' => $message->link_lampiran,
                    'file' => $message->file_lampiran
                        ? Storage::disk('public')->url($message->file_lampiran)
                        : null,
                ],
                'tugas' => $message->tugas ? [
                    'id_tugas' => $message->tugas->id_tugas,
                    'judul_tugas' => $message->tugas->judul_tugas,
                ] : null,
                'can_reply' => $isParticipant && ($root->tipe ?? 'pesan') === 'pesan',
                'balasan' => $replies->map(fn (Pesan $reply) => [
                    'id_pesan' => $reply->id_pesan,
                    'balasan_dari_id_pesan' => $reply->balasan_dari_id_pesan,
                    'judul_pesan' => $reply->judul_pesan,
                    'deskripsi' => $reply->deskripsi,
                    'tanggal_pesan' => $reply->tanggal_pesan,
                    'pengirim' => $person($reply->pengirim),
                    'penerima' => $person($reply->penerima),
                    'lampiran' => [
                        'link' => $reply->link_lampiran,
                        'file' => $reply->file_lampiran
                            ? Storage::disk('public')->url($reply->file_lampiran)
                            : null,
                    ],
                    'created_at' => $reply->created_at?->toISOString(),
                ]),
                'created_at' => $message->created_at?->toISOString(),
            ],
        ]);
    }

    public function balas(Request $request, string $id_pesan): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'deskripsi' => ['required', 'string', 'max:10000'],
            'file_lampiran' => ['nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png'],
        ]);

        $message = Pesan::with(['pengirim', 'penerima', 'balasanDari', 'tugas'])
            ->whereKey($id_pesan)
            ->where(function ($query) use ($user): void {
                $query->where('pengirim_id_user', $user->id_user)
                    ->orWhere('penerima_id_user', $user->id_user);
            })
            ->first();

        if (! $user instanceof User2 || ! $message) {
            return response()->json(['message' => 'Pesan tidak ditemukan.'], 404);
        }

        $root = $message->balasanDari ?? $message;
        if (($root->tipe ?? 'pesan') !== 'pesan') {
            return response()->json(['message' => 'Surat tidak dapat dibalas.'], 422);
        }

        $recipient = $message->pengirim_id_user === $user->id_user
            ? $message->penerima
            : $message->pengirim;

        if (! $recipient || $recipient->id_user === $user->id_user) {
            return response()->json(['message' => 'Penerima balasan tidak dapat ditentukan.'], 422);
        }

        $storedPath = $request->hasFile('file_lampiran')
            ? $request->file('file_lampiran')->store('pesan-lampiran', 'public')
            : null;

        try {
            $reply = Pesan::create([
                'id_pesan' => $this->generateMessageId(),
                'judul_pesan' => $root->judul_pesan,
                'deskripsi' => $validated['deskripsi'],
                'tipe' => 'pesan',
                'file_lampiran' => $storedPath,
                'tanggal_pesan' => now()->toDateString(),
                'tugas_id_tugas' => $root->tugas_id_tugas,
                'tugas_karyawan_id_karyawan' => $root->tugas_karyawan_id_karyawan,
                'pengirim_id_user' => $user->id_user,
                'penerima_id_user' => $recipient->id_user,
                'balasan_dari_id_pesan' => $root->id_pesan,
            ]);
        } catch (\Throwable $exception) {
            if ($storedPath) {
                Storage::disk('public')->delete($storedPath);
            }

            throw $exception;
        }

        PesanDikirim::dispatch($reply);

        return response()->json([
            'message' => 'Balasan berhasil dikirim.',
            'data' => [
                'id_pesan' => $reply->id_pesan,
                'balasan_dari_id_pesan' => $reply->balasan_dari_id_pesan,
                'tipe' => $reply->tipe,
                'judul_pesan' => $reply->judul_pesan,
                'deskripsi' => $reply->deskripsi,
                'file_lampiran' => $storedPath ? Storage::disk('public')->url($storedPath) : null,
                'pengirim_id_user' => $reply->pengirim_id_user,
                'penerima_id_user' => $reply->penerima_id_user,
            ],
        ], 201);
    }

    private function generateMessageId(): string
    {
        do {
            $id = 'PSN'.strtoupper(substr(uniqid('', true), -12));
            $id = str_replace('.', '', $id);
        } while (Pesan::whereKey($id)->exists());

        return $id;
    }
}