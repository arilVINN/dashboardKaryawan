<?php

namespace App\Http\Controllers;

use App\Models\BookingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BookingController extends Controller
{
    public const JENIS_PER_KATEGORI = [
        'Kendaraan' => ['Mobil', 'Motor'],
        'Ruangan' => ['Ruang Meeting', 'Markom', 'Ruang Writer'],
    ];

    public function store(Request $request): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'kategori' => ['required', Rule::in(['Kendaraan', 'Ruangan'])],
            'jenis' => ['required', Rule::in(array_merge(...array_values(self::JENIS_PER_KATEGORI)))],
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'keperluan' => ['required', 'string', 'max:500'],
        ])->after(function ($validator) use ($request): void {
            $validTypes = self::JENIS_PER_KATEGORI[$request->input('kategori')] ?? [];

            if ($request->filled('kategori') && $request->filled('jenis') && ! in_array($request->input('jenis'), $validTypes, true)) {
                $validator->errors()->add('jenis', 'Jenis booking tidak sesuai dengan kategori yang dipilih.');
            }
        })->validate();

        DB::transaction(function () use ($validated, $request): void {
            $conflict = BookingRequest::query()
                ->where('kategori', $validated['kategori'])
                ->where('jenis', $validated['jenis'])
                ->whereDate('tanggal', $validated['tanggal'])
                ->whereIn('status', ['Pending', 'ACC'])
                ->where('jam_mulai', '<', $validated['jam_selesai'])
                ->where('jam_selesai', '>', $validated['jam_mulai'])
                ->lockForUpdate()
                ->exists();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'jam_mulai' => "{$validated['jenis']} ({$validated['kategori']}) sudah dibooking pada rentang waktu tersebut. Silakan pilih jadwal lain.",
                ]);
            }

            BookingRequest::create([
                ...$validated,
                'id' => (string) Str::uuid(),
                'pemohon_id_user' => $request->user()->id_user,
                'status' => 'Pending',
            ]);
        });

        return back()->with('success', 'Permintaan booking berhasil dikirim dan menunggu persetujuan.');
    }

    public function index(): View
    {
        $bookings = BookingRequest::query()
            ->with(['pemohon.karyawan', 'pemroses.karyawan'])
            ->latest()
            ->paginate(15);

        return view('hrd.suratBooking', compact('bookings'));
    }

    public function approve(Request $request, BookingRequest $booking): RedirectResponse
    {
        return $this->process($request, $booking, 'ACC');
    }

    public function reject(Request $request, BookingRequest $booking): RedirectResponse
    {
        return $this->process($request, $booking, 'Tolak');
    }

    private function process(Request $request, BookingRequest $booking, string $status): RedirectResponse
    {
        $updated = BookingRequest::query()
            ->whereKey($booking->getKey())
            ->where('status', 'Pending')
            ->update([
                'status' => $status,
                'diproses_oleh_id_user' => $request->user()->id_user,
                'diproses_pada' => now(),
                'updated_at' => now(),
            ]);

        abort_unless($updated === 1, 409, 'Permintaan booking sudah diproses.');

        return back()->with('success', $status === 'ACC'
            ? 'Booking berhasil disetujui.'
            : 'Booking berhasil ditolak.');
    }
}
