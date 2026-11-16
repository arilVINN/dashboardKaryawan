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
        Schema::table('surat_izin', function (Blueprint $table) {
            $table->string('jenis_izin')->nullable()->after('karyawan_id_karyawan');
            $table->date('tanggal_mulai')->nullable()->after('judul_surat');
            $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
        });
    }

    public function down(): void
    {
        Schema::table('surat_izin', function (Blueprint $table) {
            $table->dropColumn(['jenis_izin', 'tanggal_mulai', 'tanggal_selesai']);
        });
    }
};
