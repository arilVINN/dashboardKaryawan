<?php

use App\Models\Jabatan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Turn every distinct (division, jabatan text) pair already in use into a
     * jabatan row and link the affected employees to it.
     *
     * A name used in several divisions becomes one row per division. Anything
     * that cannot be mapped (missing division or empty jabatan) is reported
     * in storage/app/private/jabatan-migration-report.json.
     */
    public function up(): void
    {
        $rows = DB::table('karyawans')
            ->select('id_karyawan', 'divisi_id_divisi', 'jabatan')
            ->orderBy('divisi_id_divisi')
            ->orderBy('jabatan')
            ->get();

        $created = 0;
        $linked = 0;
        $unmapped = [];

        $divisionIds = DB::table('divisis')->pluck('id_divisi')->flip();

        $pairs = $rows->groupBy(fn ($row) => trim((string) $row->divisi_id_divisi).'|'.trim((string) $row->jabatan));

        foreach ($pairs as $key => $group) {
            /** @var Collection $group */
            $divisionId = trim((string) $group->first()->divisi_id_divisi);
            $nama = $this->normalise((string) $group->first()->jabatan);

            if ($divisionId === '' || ! $divisionIds->has($divisionId) || $nama === '') {
                foreach ($group as $row) {
                    $unmapped[] = [
                        'id_karyawan' => $row->id_karyawan,
                        'divisi_id_divisi' => $row->divisi_id_divisi,
                        'jabatan' => $row->jabatan,
                        'alasan' => $divisionId === '' || ! $divisionIds->has($divisionId)
                            ? 'Divisi tidak ditemukan'
                            : 'Nama jabatan kosong',
                    ];
                }

                continue;
            }

            $jabatan = Jabatan::firstOrCreate(
                ['division_id' => $divisionId, 'nama' => $nama],
                ['aktif' => true],
            );

            if ($jabatan->wasRecentlyCreated) {
                $created++;
            }

            foreach ($group as $row) {
                DB::table('karyawans')
                    ->where('id_karyawan', $row->id_karyawan)
                    ->update(['jabatan_id' => $jabatan->id, 'jabatan' => $jabatan->nama]);
                $linked++;
            }
        }

        $report = [
            'generated_at' => now()->toIso8601String(),
            'jabatan_created' => $created,
            'karyawan_linked' => $linked,
            'unmapped_count' => count($unmapped),
            'unmapped' => $unmapped,
        ];

        Storage::disk('local')->put('jabatan-migration-report.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if ($unmapped !== []) {
            info('Migrasi jabatan: '.count($unmapped).' data tidak dapat dipetakan. Lihat storage/app/private/jabatan-migration-report.json');
        }
    }

    public function down(): void
    {
        DB::table('karyawans')->update(['jabatan_id' => null]);
        DB::table('jabatan')->delete();
    }

    private function normalise(string $nama): string
    {
        return trim(preg_replace('/\s+/', ' ', $nama) ?? '');
    }
};
