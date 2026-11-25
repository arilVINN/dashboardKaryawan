<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * A jabatan grants a role; the employee's role follows the position.
     * Jobdesk is descriptive only (a keterangan for staff), never a permission.
     */
    public function up(): void
    {
        Schema::table('jabatan', function (Blueprint $table): void {
            $table->string('role_id_role', 20)->nullable()->after('nama');
            $table->foreign('role_id_role')
                ->references('id_role')
                ->on('roles')
                ->nullOnDelete();
        });

        Schema::table('karyawans', function (Blueprint $table): void {
            $table->string('jobdesk', 50)->nullable()->after('job_level');
        });

        $roles = DB::table('roles')->pluck('id_role', 'nama_role');
        $cariRole = function (string $nama) use ($roles): ?string {
            foreach ($roles as $namaRole => $idRole) {
                if (strtolower((string) $namaRole) === strtolower($nama)) {
                    return (string) $idRole;
                }
            }

            return null;
        };

        $terpetakan = 0;
        $tidakTerpetakan = [];

        foreach (DB::table('jabatan')->get(['id', 'nama']) as $jabatan) {
            $nama = mb_strtolower(trim((string) $jabatan->nama));
            $namaRole = match (true) {
                str_starts_with($nama, 'direktur utama') => 'direktur',
                str_starts_with($nama, 'kepala divisi') => 'kadiv',
                str_contains($nama, 'hrd') => 'hrd',
                str_starts_with($nama, 'staff'),
                str_starts_with($nama, 'markom'),
                str_starts_with($nama, 'marcom'),
                str_starts_with($nama, 'layouter') => 'staff',
                default => 'staff',
            };

            $roleId = $cariRole($namaRole);

            if (! $roleId) {
                $tidakTerpetakan[] = [
                    'jabatan_id' => $jabatan->id,
                    'nama' => $jabatan->nama,
                    'alasan' => 'Role '.$namaRole.' tidak ditemukan',
                ];

                continue;
            }

            DB::table('jabatan')->where('id', $jabatan->id)->update(['role_id_role' => $roleId]);
            $terpetakan++;
        }

        Storage::disk('local')->put('jabatan-role-backfill-report.json', json_encode([
            'generated_at' => now()->toIso8601String(),
            'jabatan_terpetakan' => $terpetakan,
            'tidak_terpetakan' => $tidakTerpetakan,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table): void {
            $table->dropColumn('jobdesk');
        });

        Schema::table('jabatan', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('role_id_role');
        });
    }
};
