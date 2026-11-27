<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_izin', function (Blueprint $table) {
            $table->date('tanggal_pengajuan')->nullable()->after('karyawan_id_karyawan');
            $table->string('keperluan', 20)->nullable()->after('jenis_izin');
            $table->string('kategori_pengajuan', 30)->nullable()->after('keperluan');
        });
    }

    public function down(): void
    {
        Schema::table('surat_izin', function (Blueprint $table) {
            $table->dropColumn(['tanggal_pengajuan', 'keperluan', 'kategori_pengajuan']);
        });
    }
};
