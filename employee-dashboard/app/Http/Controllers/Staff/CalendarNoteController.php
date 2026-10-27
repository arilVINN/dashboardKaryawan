<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\CalendarNote;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CalendarNoteController extends Controller
{
    /**
     * Ambil semua note milik user yang sedang login (untuk range bulan kalender).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user() ?: auth()->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $start = $request->input('start');
        $end   = $request->input('end');

        $query = CalendarNote::where('user_id_user', $user->id_user);

        if ($start) {
            $query->where('tanggal', '>=', Carbon::parse($start)->toDateString());
        }
        if ($end) {
            $query->where('tanggal', '<=', Carbon::parse($end)->toDateString());
        }

        $notes = $query->orderBy('tanggal')->get();

        $events = $notes->map(function (CalendarNote $note) {
            $warna = CalendarNote::WARNA_OPTIONS[$note->warna] ?? CalendarNote::WARNA_OPTIONS['slate'];

            return [
                'id'              => 'NOTE-' . $note->id,
                'title'           => $note->judul,
                'start'           => $note->tanggal->toDateString(),
                'allDay'          => true,
                'backgroundColor' => $warna['bg'],
                'borderColor'     => $warna['border'],
                'textColor'       => $warna['text'],
                'extendedProps'   => [
                    'type'       => 'note',
                    'note_id'    => $note->id,
                    'judul'      => $note->judul,
                    'detail'     => $note->detail,
                    'warna'      => $note->warna,
                    'dot_color'  => $warna['dot'],
                    'tanggal_label' => $note->tanggal->locale('id')->isoFormat('dddd, D MMMM Y'),
                ],
            ];
        });

        return response()->json(['data' => $events]);
    }

    /**
     * Simpan note baru.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user() ?: auth()->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'judul'   => 'required|string|max:255',
            'detail'  => 'nullable|string|max:1000',
            'warna'   => 'nullable|string|in:' . implode(',', array_keys(CalendarNote::WARNA_OPTIONS)),
        ]);

        $note = CalendarNote::create([
            'user_id_user' => $user->id_user,
            'tanggal'      => Carbon::parse($validated['tanggal'])->toDateString(),
            'judul'        => $validated['judul'],
            'detail'       => $validated['detail'] ?? null,
            'warna'        => $validated['warna'] ?? 'slate',
        ]);

        $warna = CalendarNote::WARNA_OPTIONS[$note->warna] ?? CalendarNote::WARNA_OPTIONS['slate'];

        return response()->json([
            'message' => 'Note berhasil disimpan',
            'event'   => [
                'id'              => 'NOTE-' . $note->id,
                'title'           => $note->judul,
                'start'           => $note->tanggal->toDateString(),
                'allDay'          => true,
                'backgroundColor' => $warna['bg'],
                'borderColor'     => $warna['border'],
                'textColor'       => $warna['text'],
                'extendedProps'   => [
                    'type'          => 'note',
                    'note_id'       => $note->id,
                    'judul'         => $note->judul,
                    'detail'        => $note->detail,
                    'warna'         => $note->warna,
                    'dot_color'     => $warna['dot'],
                    'tanggal_label' => $note->tanggal->locale('id')->isoFormat('dddd, D MMMM Y'),
                ],
            ],
        ], 201);
    }

    /**
     * Hapus note milik user.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user() ?: auth()->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $note = CalendarNote::where('id', $id)
            ->where('user_id_user', $user->id_user)
            ->first();

        if (! $note) {
            return response()->json(['message' => 'Note tidak ditemukan'], 404);
        }

        $note->delete();

        return response()->json(['message' => 'Note berhasil dihapus']);
    }
}
