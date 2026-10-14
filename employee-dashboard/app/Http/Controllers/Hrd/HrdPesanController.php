<?php

namespace App\Http\Controllers\Hrd;

use App\Http\Controllers\Controller;
use App\Events\PesanDikirim;
use App\Models\Pesan;
use App\Models\User2;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HrdPesanController extends Controller
{
    public function page(Request $request)
    {
        $user = $request->user();
        $rows = $this->messageRows($this->allMessages(), $user);

        return view('hrd.pesan', [
            'pesans' => $this->paginateRows($this->applyListFilters($rows, $request), $request),
            'perPage' => $this->perPage($request),
            'belumDibaca' => $rows->where('unread', true)->count(),
            'totalPesan' => $rows->count(),
            'pesanMasuk' => $rows->where('arah', 'masuk')->count(),
            'pesanKeluar' => $rows->where('arah', 'keluar')->count(),
        ]);
    }

    public function daftarPage(Request $request)
    {
        $user = $request->user();
        $rows = $this->messageRows($this->allMessages(), $user);

        return view('hrd.daftarPesan', [
            'pesans' => $this->paginateRows($this->applyListFilters($rows, $request), $request),
            'perPage' => $this->perPage($request),
            'belumDibaca' => $rows->where('unread', true)->count(),
        ]);
    }

    /**
     * Viewer-relative rows for the Daftar Pesan list: counterparty display
     * name, subject preview, relative date and read state.
     */
    private function messageRows($pesans, $user)
    {
        return $pesans->map(fn ($pesan) => [
            'id_pesan' => $pesan->id_pesan,
            'judul' => $pesan->judul_pesan,
            'deskripsi' => $pesan->deskripsi,
            'preview' => Str::limit((string) ($pesan->deskripsi ?? ''), 90),
            'arah' => $pesan->pengirim_id_user !== $user->id_user ? 'masuk' : 'keluar',
            'kontak_nama' => $this->contactName($pesan, $user),
            'unread' => $pesan->pengirim_id_user !== $user->id_user && empty($pesan->dibaca_at),
            'tanggal_raw' => $pesan->tanggal_pesan,
            'tanggal_relatif' => Pesan::formatTanggalRelatif($pesan->tanggal_pesan, $pesan->created_at),
            'tanggal_lengkap' => Pesan::formatTanggalJam($pesan->tanggal_pesan, $pesan->created_at),
        ])->values();
    }

    private function contactName($pesan, $user): string
    {
        $contact = $pesan->pengirim_id_user !== $user->id_user ? $pesan->pengirim : $pesan->penerima;

        return $contact?->karyawan?->nama ?? $contact?->username ?? 'Pengguna tidak tersedia';
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 10);

        return in_array($perPage, [10, 25, 50], true) ? $perPage : 10;
    }

    private function paginateRows($rows, Request $request)
    {
        $perPage = $this->perPage($request);
        $page = max(1, (int) $request->query('page', 1));

        return new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    private function applyListFilters($rows, Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $needle = mb_strtolower($q);
            $rows = $rows->filter(fn ($row) => str_contains(mb_strtolower((string) ($row['judul'] ?? '')), $needle)
                || str_contains(mb_strtolower((string) ($row['deskripsi'] ?? '')), $needle)
                || str_contains(mb_strtolower((string) ($row['kontak_nama'] ?? '')), $needle));
        }

        // Inbox model: everything not sent by the viewer counts as masuk,
        // so masuk + keluar always equals the total.
        $arah = $request->query('arah');
        if ($arah === 'masuk') {
            $rows = $rows->where('arah', 'masuk');
        } elseif ($arah === 'keluar') {
            $rows = $rows->where('arah', 'keluar');
        }

        $dari = $request->query('dari');
        if (is_string($dari) && $dari !== '') {
            $rows = $rows->filter(fn ($row) => ($row['tanggal_raw'] ?? '') >= $dari);
        }

        $sampai = $request->query('sampai');
        if (is_string($sampai) && $sampai !== '') {
            $rows = $rows->filter(fn ($row) => ($row['tanggal_raw'] ?? '') <= $sampai);
        }

        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';
        if ($request->query('sort') === 'judul') {
            $rows = $dir === 'asc'
                ? $rows->sortBy(fn ($row) => mb_strtolower($row['judul'] ?? ''))
                : $rows->sortByDesc(fn ($row) => mb_strtolower($row['judul'] ?? ''));
        } elseif ($request->query('sort') === 'pengirim') {
            $rows = $dir === 'asc'
                ? $rows->sortBy(fn ($row) => mb_strtolower($row['kontak_nama'] ?? ''))
                : $rows->sortByDesc(fn ($row) => mb_strtolower($row['kontak_nama'] ?? ''));
        } elseif ($request->query('sort') === 'tanggal') {
            $rows = $dir === 'asc'
                ? $rows->sortBy(fn ($row) => [$row['tanggal_raw'], $row['tanggal_lengkap']])
                : $rows->sortByDesc(fn ($row) => [$row['tanggal_raw'], $row['tanggal_lengkap']]);
        }

        return $rows->values();
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

        // Opening a message marks it read for the recipient (first time only).
        if ($pesan->penerima_id_user === $user->id_user && $pesan->dibaca_at === null) {
            $pesan->update(['dibaca_at' => now()]);
        }

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