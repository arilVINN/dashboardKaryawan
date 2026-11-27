<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('divisis', function (Blueprint $table) {
            if (! Schema::hasColumn('divisis', 'posisi_bagan')) {
                $table->string('posisi_bagan', 30)
                    ->default('operasional')
                    ->after('urutan')
                    ->comment('dirut_pendukung | dirops_pendukung | operasional');
            }
        });

        // Set default posisi_bagan untuk divisi pendukung yang sudah ada
        DB::table('divisis')
            ->whereIn(DB::raw('UPPER(kode_divisi)'), ['HR', 'FIN'])
            ->orWhereIn(DB::raw('LOWER(nama_divisi)'), ['hr', 'hr & ga', 'finance', 'finance & accounting'])
            ->update(['posisi_bagan' => 'dirops_pendukung']);

        DB::table('divisis')
            ->whereIn(DB::raw('UPPER(kode_divisi)'), ['LEGAL', 'PA', 'WRITER'])
            ->orWhereIn(DB::raw('LOWER(nama_divisi)'), ['legal', 'legal & compliance', 'personal assistant', 'writer'])
            ->update(['posisi_bagan' => 'dirut_pendukung']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('divisis', function (Blueprint $table) {
            if (Schema::hasColumn('divisis', 'posisi_bagan')) {
                $table->dropColumn('posisi_bagan');
            }
        });
    }
};
