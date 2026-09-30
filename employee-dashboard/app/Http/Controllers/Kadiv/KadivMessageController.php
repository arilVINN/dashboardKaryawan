<?php

namespace App\Http\Controllers\Kadiv;

use App\Events\PesanDikirim;
use App\Http\Controllers\Controller;
use App\Models\Pesan;
use App\Models\Tugas;
use App\Models\User2;
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
        ]);

        $query = Pesan::query()
            ->with(['pengirim.karyawan', 'penerima.karyawan', 'tugas'])
            ->where(function ($query) use ($user): void {
                $query->where('pengirim_id_user', $user->id_user)
                    ->orWhere('penerima_id_user', $user->id_user);
            });

        if (isset($validated['tipe'])) {
            $query->where('tipe', $validated['tipe']);
        }

        if (($validated['arah'] ?? null) === 'masuk') {
            $query->where('penerima_id_user', $user->id_user);
        } elseif (($validated['arah'] ?? null) === 'keluar') {
            $query->where('pengirim_id_user', $user->id_user);
        }

        $messages = $query->latest('created_at')->paginate($validated['per_page'] ?? 15);

        return response()->json([
            'data' => collect($messages->items())->map(
                fn (Pesan $message) => $this->messageData($message, $user)
            ),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
        ]);
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
            'data' => $this->messageData($message, $user),
        ], 201);
    }

    private function messageData(Pesan $message, User2 $user): array
    {
        $person = fn (?User2 $account) => $account ? [
            'id_user' => $account->id_user,
            'username' => $account->username,
            'nama' => $account->karyawan?->nama,
        ] : null;

        return [
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
            'created_at' => $message->created_at?->toISOString(),
        ];
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
