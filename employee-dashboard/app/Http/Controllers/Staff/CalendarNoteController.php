<?php

namespace App\Http\Controllers\Staff;

use App\Actions\SendCalendarNoteReminder;
use App\Http\Controllers\Controller;
use App\Models\CalendarNote;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
                    'reminder_days_before' => $note->reminder_days_before,
                    'reminder_label' => $note->reminderLabel(),
                    'reminder_date'  => $note->reminderDate()?->toDateString(),
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
            'reminder_days_before' => 'nullable|integer|min:0|max:' . CalendarNote::REMINDER_MAX_DAYS,
        ]);

        // Samakan validasi dengan UI: pengingat yang tanggalnya sudah lewat
        // tidak boleh disimpan karena tidak akan pernah muncul.
        if (array_key_exists('reminder_days_before', $validated) && $validated['reminder_days_before'] !== null) {
            $reminderDate = Carbon::parse($validated['tanggal'])
                ->subDays((int) $validated['reminder_days_before'])
                ->startOfDay();

            if ($reminderDate->lt(Carbon::today())) {
                throw ValidationException::withMessages([
                    'reminder_days_before' => 'Tanggal pengingat sudah lewat. Pilih waktu yang lebih singkat.',
                ]);
            }
        }

        $note = CalendarNote::create([
            'user_id_user' => $user->id_user,
            'tanggal'      => Carbon::parse($validated['tanggal'])->toDateString(),
            'judul'        => $validated['judul'],
            'detail'       => $validated['detail'] ?? null,
            'warna'        => $validated['warna'] ?? 'slate',
            'reminder_days_before' => array_key_exists('reminder_days_before', $validated)
                ? $validated['reminder_days_before']
                : CalendarNote::REMINDER_DEFAULT_DAYS,
        ]);

        $warna = CalendarNote::WARNA_OPTIONS[$note->warna] ?? CalendarNote::WARNA_OPTIONS['slate'];

        $this->sendImmediateReminderIfDue($note);

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
                    'reminder_days_before' => $note->reminder_days_before,
                    'reminder_label' => $note->reminderLabel(),
                    'reminder_date'  => $note->reminderDate()?->toDateString(),
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

    /**
     * Kirim email langsung bila pengingat catatan jatuh hari ini dan jadwal
     * harian (08:00) sudah lewat. Kegagalan email tidak boleh menggagalkan
     * penyimpanan catatan.
     */
    private function sendImmediateReminderIfDue(CalendarNote $note): void
    {
        if (! $note->remindsOn(now())) {
            return;
        }

        if (now()->lt(now()->startOfDay()->addHours(8))) {
            return;
        }

        try {
            app(SendCalendarNoteReminder::class)->handle($note);
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim email pengingat catatan saat dibuat: '.$e->getMessage(), [
                'id_catatan' => $note->id,
                'id_user' => $note->user_id_user,
            ]);
        }
    }
}
