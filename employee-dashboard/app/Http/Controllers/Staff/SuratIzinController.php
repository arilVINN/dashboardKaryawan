<?php

namespace App\Http\Controllers\Staff;

// app/Http/Controllers/Staff/SuratIzinController.php
use App\Actions\NotifyHrdUsers;
use App\Actions\NotifyKadivUsers;
use App\Http\Controllers\Controller;
use App\Models\SuratIzin;
use App\Models\User2;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SuratIzinController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $suratIzinStatus = SuratIzin::query()
            ->where('karyawan_id_karyawan', $user->karyawan_id_karyawan)
            ->latest()
            ->get();

        return view('staff.suratIzin', compact('suratIzinStatus'));
    }

    public function store(Request $request)
    {
        $this->normalizeLegacyPayload($request);
        $this->normalizeJamPayload($request);

        $data = $request->validate([
            'tanggal_pengajuan' => 'required|date|before_or_equal:today',
            'keperluan' => ['required', 'string', Rule::in(SuratIzin::KEPERLUAN_OPTIONS)],
            'jenis_pengajuan' => ['required', 'string', Rule::in(SuratIzin::jenisPengajuanOptions())],
            'jam_pengajuan' => [
                'nullable',
                'date_format:H:i',
                Rule::requiredIf(fn () => in_array(
                    (string) $request->input('jenis_pengajuan'),
                    SuratIzin::JENIS_DENGAN_JAM,
                    true
                )),
            ],
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'judul_surat' => 'nullable|string|max:100',
            'file' => 'nullable|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        $karyawanId = auth()->user()->karyawan_id_karyawan;
        $kategori = SuratIzin::kategoriUntukJenis($data['jenis_pengajuan']);

        $user = auth()->user();
        $employee = $user->karyawan;
        $divisionId = $employee?->divisi_id_divisi;
        $hasKadiv = $divisionId !== null && User2::query()
            ->where(function ($query) {
                $query->whereHas('role', fn ($roleQuery) => $roleQuery
                    ->whereRaw('LOWER(nama_role) = ?', ['kadiv']))
                    ->orWhereHas('roles', fn ($roleQuery) => $roleQuery
                        ->whereRaw('LOWER(nama_role) = ?', ['kadiv']));
            })
            ->whereHas('karyawan', fn ($query) => $query
                ->where('divisi_id_divisi', $divisionId))
            ->exists();
        $tahap = (! $user->hasRole('kadiv') && $hasKadiv) ? 'kadiv' : 'hrd';

        do {
            $newId = 'SRT'.strtoupper(Str::random(3));
        } while (SuratIzin::whereKey($newId)->exists());

        $judulSurat = $request->filled('judul_surat')
            ? (string) $request->input('judul_surat')
            : SuratIzin::judulUntuk($kategori, $data['jenis_pengajuan']);

        $suratIzin = SuratIzin::create([
            'id_surat' => $newId,
            'karyawan_id_karyawan' => $karyawanId,
            'tanggal_pengajuan' => $data['tanggal_pengajuan'],
            'jenis_izin' => $data['jenis_pengajuan'],
            'jam_pengajuan' => in_array($data['jenis_pengajuan'], SuratIzin::JENIS_DENGAN_JAM, true)
                ? ($data['jam_pengajuan'] ?? null)
                : null,
            'keperluan' => $data['keperluan'],
            'kategori_pengajuan' => $kategori,
            'judul_surat' => $judulSurat,
            'tanggal_izin' => $data['tanggal_mulai'], // kolom lama tetap diisi
            'tanggal_mulai' => $data['tanggal_mulai'],
            'tanggal_selesai' => $data['tanggal_selesai'],
            'keterangan' => $data['keterangan'] ?? null,
            'file' => $request->hasFile('file')
                ? $request->file('file')->store('surat_izin', 'private') : null,
            'status' => 'Pending',
            'tahap' => $tahap,
        ]);

        $namaPemohon = $employee?->nama ?? $user->username;

        if ($tahap === 'kadiv') {
            app(NotifyKadivUsers::class)(
                'Surat Izin Menunggu Persetujuan',
                'Surat izin "'.$suratIzin->judul_surat.'" ('.$suratIzin->jenis_izin.') dari '
                    .$namaPemohon.' menunggu persetujuan Kepala Divisi.',
                $divisionId
            );
        } else {
            app(NotifyHrdUsers::class)(
                'Surat Izin Masuk',
                'Surat izin "'.$suratIzin->judul_surat.'" ('.$suratIzin->jenis_izin.') dari '
                    .$namaPemohon.' menunggu persetujuan HRD.'
            );
        }

        return back()->with('success', 'Surat izin berhasil dikirim.');
    }

    /**
     * Dukung payload lama (tanggal_izin / judul_surat) agar tautan atau klien
     * lama tidak langsung gagal setelah form baru diperkenalkan.
     */
    private function normalizeLegacyPayload(Request $request): void
    {
        if ($request->filled('jenis_pengajuan')) {
            return;
        }

        if (! $request->filled('tanggal_izin') && ! $request->filled('judul_surat')) {
            return;
        }

        $legacyJenis = (string) ($request->input('jenis_izin') ?? $request->input('judul_surat') ?? '');
        $tanggal = $request->input('tanggal_izin') ?? $request->input('tanggal_mulai');

        $request->merge([
            'jenis_pengajuan' => $this->mapLegacyJenis($legacyJenis),
            'tanggal_pengajuan' => $request->input('tanggal_pengajuan') ?? now()->toDateString(),
            'keperluan' => $request->input('keperluan') ?? 'Pribadi',
            'tanggal_mulai' => $request->input('tanggal_mulai') ?? $tanggal,
            'tanggal_selesai' => $request->input('tanggal_selesai') ?? $tanggal,
        ]);
    }

    private function mapLegacyJenis(string $jenis): string
    {
        $value = Str::lower($jenis);

        return match (true) {
            str_contains($value, 'sakit') => 'Sakit',
            str_contains($value, 'tahunan') => 'Tahunan',
            str_contains($value, 'menikah') => 'Menikah',
            str_contains($value, 'istri') && str_contains($value, 'melahirkan') => 'Menunggu Istri Melahirkan',
            str_contains($value, 'melahirkan') => 'Melahirkan',
            str_contains($value, 'dinas') => 'Perjalanan Dinas Luar Kota',
            default => 'Tidak Masuk',
        };
    }

    /**
     * Terima jam yang diketik manual ("8:30", "0830", "8.30") lalu
     * normalkan menjadi HH:MM sebelum divalidasi.
     */
    private function normalizeJamPayload(Request $request): void
    {
        if (! $request->filled('jam_pengajuan')) {
            return;
        }

        $value = trim((string) $request->input('jam_pengajuan'));
        $value = str_replace(['.', ',', ' '], ':', $value);

        if (preg_match('/^(\d{1,2}):(\d{1,2})(?::\d{1,2})?$/', $value, $matches)) {
            $hour = (int) $matches[1];
            $minute = (int) $matches[2];

            if ($hour <= 23 && $minute <= 59) {
                $value = sprintf('%02d:%02d', $hour, $minute);
            }
        } elseif (preg_match('/^\d{3,4}$/', $value)) {
            $digits = str_pad($value, 4, '0', STR_PAD_LEFT);
            $hour = (int) substr($digits, 0, 2);
            $minute = (int) substr($digits, 2, 2);

            if ($hour <= 23 && $minute <= 59) {
                $value = sprintf('%02d:%02d', $hour, $minute);
            }
        }

        $request->merge(['jam_pengajuan' => $value]);
    }
}
