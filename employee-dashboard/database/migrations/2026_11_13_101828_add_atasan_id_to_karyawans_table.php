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
        Schema::table('karyawans', function (Blueprint $table) {
            $table->string('atasan_id', 20)->nullable()->after('divisi_id_divisi');
            $table->foreign('atasan_id')
                ->references('id_karyawan')
                ->on('karyawans')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropForeign(['atasan_id']);
            $table->dropColumn('atasan_id');
        });
    }
};
