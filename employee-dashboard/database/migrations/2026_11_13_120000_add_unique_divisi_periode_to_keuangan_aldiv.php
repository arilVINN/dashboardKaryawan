<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satu divisi hanya boleh memiliki satu alokasi pagu per bulan.
     */
    public function up(): void
    {
        Schema::table('keuangan_aldiv', function (Blueprint $table) {
            $table->unique(
                ['divisi_id_divisi', 'periode'],
                'keuangan_aldiv_divisi_periode_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('keuangan_aldiv', function (Blueprint $table) {
            $table->dropUnique('keuangan_aldiv_divisi_periode_unique');
        });
    }
};
