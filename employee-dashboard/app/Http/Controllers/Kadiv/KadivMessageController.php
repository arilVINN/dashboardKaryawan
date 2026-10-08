<?php

namespace App\Http\Controllers\Kadiv;

use App\Events\PesanDikirim;
use App\Http\Controllers\Controller;
use App\Models\Pesan;
use App\Models\Tugas;
use App\Models\User2;
use App\Presenters\MessagePresenter;
use App\Queries\KadivMessageQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class KadivMessageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->authenticatedKadiv($request);
        if (! $user) {
            return $this->unauthorizedResponse($request);
        }

        $validated = $request->validate([
            'tipe' => ['nullable', Rule::in(['pesan', 'surat'])],
            'arah' => ['nullable', Rule::in(['masuk', 'keluar'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sort' => ['nullable', Rule::in(['tanggal', 'judul', 'pengirim'])],
            'dir' => ['nullable', Rule::in(['asc', 'desc'])],
            'q' => ['nullable', 'string', 'max:200'],
        ]);

        $messages = KadivMessageQuery::forUser($user)
            ->apply($request->only(['tipe', 'arah', 'sort', 'dir', 'q']))
            ->paginate($validated['per_page'] ?? 15);

        return response()->json([
            'data' => collect($messages->items())->map(
                fn (Pesan $message) => MessagePresenter::for($message, $user)
            ),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
        ]);
    }

    public function show(Request $request, string $id_pesan): JsonResponse
    {
        $user = $this->authenticatedKadiv($request);
        if (! $user) {
            return $this->unauthorizedResponse($request);
        }

        $message = Pesan::query()
            ->with(['pengirim.karyawan', 'penerima.karyawan', 'tugas.karyawan', 'balasan.pengirim.karyawan', 'balasan.penerima.karyawan', 'balasanDari'])
            ->whereKey($id_pesan)
            ->where(function ($query) use ($user): void {
                $query->where('pengirim_id_user', $user->id_user)
                    ->orWhere('penerima_id_user', $user->id_user)
                    ->orWhereHas('tugas.karyawan', fn ($employeeQuery) =>
                        $employeeQuery->where('divisi_id_divisi', $user->karyawan->divisi_id_divisi)
                    );
            })
            ->first();

        if (! $message) {
            return response()->json(['message' => 'Pesan tidak ditemukan.'], 404);
        }

        $root = $message->balasanDari ?? $message;
        $data = MessagePresenter::for($message, $user);
        $data['can_reply'] = ($root->tipe ?? 'pesan') === 'pesan';
        $data['balasan'] = ($root->id_pesan === $message->id_pesan
            ? $message->balasan
            : $root->balasan
        )->map(fn (Pesan $reply) => MessagePresenter::for($reply, $user));

        return response()->json([
            'data' => $data,
        ]);
    }

    public function balas(Request $request, string $id_pesan): JsonResponse
    {
        $user = $this->authenticatedKadiv($request);
        if (! $user) {
            return $this->unauthorizedResponse($request);
        }

        $validated = $request->validate([
            'deskripsi' => ['required', 'string', 'max:10000'],
            'file_lampiran' => ['nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png'],
        ]);

        $message = Pesan::query()
            ->with(['pengirim', 'penerima', 'balasanDari', 'tugas.karyawan.user'])
            ->whereKey($id_pesan)
            ->where(function ($query) use ($user): void {
                $query->where('pengirim_id_user', $user->id_user)
                    ->orWhere('penerima_id_user', $user->id_user)
                    ->orWhereHas('tugas.karyawan', fn ($employeeQuery) =>
                        $employeeQuery->where('divisi_id_divisi', $user->karyawan->divisi_id_divisi)
                    );
            })
            ->first();

        if (! $message) {
            return response()->json(['message' => 'Pesan tidak ditemukan.'], 404);
        }

        $root = $message->balasanDari ?? $message;
        if (($root->tipe ?? 'pesan') !== 'pesan') {
            return response()->json(['message' => 'Surat tidak dapat dibalas.'], 422);
        }

        $recipient = $this->resolveReplyRecipient($message, $user);
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

    private function resolveReplyRecipient(Pesan $message, User2 $user): ?User2
    {
        if ($message->pengirim_id_user === $user->id_user && $message->penerima) {
            return $message->penerima;
        }

        if ($message->penerima_id_user === $user->id_user && $message->pengirim) {
            return $message->pengirim;
        }

        return $message->tugas?->karyawan?->user;
    }

    public function store(Request $request): JsonResponse
    {
        $user = $this->authenticatedKadiv($request);
        if (! $user) {
            return $this->unauthorizedResponse($request);
        }

        $validated = $request->validate([
            'penerima_id_user' => ['required', 'string', 'exists:users2,id_user'],
            'tipe' => ['required', Rule::in(['pesan', 'surat'])],
            'judul_pesan' => ['required', 'string', 'max:200'],
            'deskripsi' => ['required', 'string'],
            'tugas_id_tugas' => ['nullable', 'string', 'exists:tugas,id_tugas'],
            'link_lampiran' => ['nullable', 'url', 'max:2048'],
            'file_lampiran' => ['nullable', 'file', 'max:5120', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png'],
        ]);

        $recipient = User2::with(['role', 'karyawan'])->findOrFail($validated['penerima_id_user']);
        $recipientRole = strtolower($recipient->role?->nama_role ?? '');
        $sameDivisionStaff = $recipientRole === 'staff'
            && $recipient->karyawan?->divisi_id_divisi === $user->karyawan->divisi_id_divisi;

        if (! $sameDivisionStaff && $recipientRole !== 'hrd') {
            return response()->json([
                'message' => 'Penerima harus Staff di divisi Anda atau HRD.',
            ], 422);
        }

        $task = null;
        if (isset($validated['tugas_id_tugas'])) {
            $task = Tugas::with('karyawan')->find($validated['tugas_id_tugas']);
            if ($task?->karyawan?->divisi_id_divisi !== $user->karyawan->divisi_id_divisi
                || ($sameDivisionStaff && $task->karyawan_id_karyawan !== $recipient->karyawan_id_karyawan)) {
                return response()->json([
                    'message' => 'Tugas tidak sesuai dengan divisi atau Staff penerima.',
                ], 422);
            }
        }

        $storedPath = null;
        try {
            if ($request->hasFile('file_lampiran')) {
                $storedPath = $request->file('file_lampiran')->store('pesan-lampiran', 'public');
            }

            $message = DB::transaction(fn () => Pesan::create([
                'id_pesan' => $this->generateMessageId(),
                'judul_pesan' => $validated['judul_pesan'],
                'deskripsi' => $validated['deskripsi'],
                'tipe' => $validated['tipe'],
                'link_lampiran' => $validated['link_lampiran'] ?? null,
                'file_lampiran' => $storedPath,
                'tanggal_pesan' => now()->toDateString(),
                'tugas_id_tugas' => $task?->id_tugas,
                'tugas_karyawan_id_karyawan' => $task?->karyawan_id_karyawan,
                'pengirim_id_user' => $user->id_user,
                'penerima_id_user' => $recipient->id_user,
            ]));
        } catch (\Throwable $exception) {
            if ($storedPath) {
                Storage::disk('public')->delete($storedPath);
            }
            throw $exception;
        }

        PesanDikirim::dispatch($message);
        $message->load(['pengirim.karyawan', 'penerima.karyawan', 'tugas']);

        return response()->json([
            'message' => ucfirst($validated['tipe']).' berhasil dikirim.',
            'data' => MessagePresenter::for($message, $user),
        ], 201);
    }


    private function authenticatedKadiv(Request $request): ?User2
    {
        $user = $request->user();
        if (! $user instanceof User2) {
            return null;
        }

        $user->loadMissing(['role', 'karyawan.divisi']);

        return strtolower($user->role?->nama_role ?? '') === 'kadiv' && $user->karyawan?->divisi
            ? $user
            : null;
    }

    private function unauthorizedResponse(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $request->user()
                ? 'Akses hanya diberikan kepada Kadiv.'
                : 'User Kadiv belum terautentikasi.',
        ], $request->user() ? 403 : 401);
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
