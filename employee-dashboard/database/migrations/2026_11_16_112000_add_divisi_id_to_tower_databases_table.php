<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tower_databases', function (Blueprint $table) {
            $table->string('divisi_id_divisi', 20)->nullable()->after('dibuat_oleh_id_karyawan');

            $table->foreign('divisi_id_divisi')
                ->references('id_divisi')
                ->on('divisis')
                ->nullOnDelete();

            $table->index('divisi_id_divisi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tower_databases', function (Blueprint $table) {
            $table->dropForeign(['divisi_id_divisi']);
            $table->dropIndex(['divisi_id_divisi']);
            $table->dropColumn('divisi_id_divisi');
        });
    }
};
