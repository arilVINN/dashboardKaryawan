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
        Schema::create('surat_izin', function (Blueprint $t) {
            $t->string('id_surat', 6)->primary();
            $t->string('karyawan_id_karyawan', 10);
            $t->string('judul_surat', 100);
            $t->date('tanggal_izin');
            $t->string('file')->nullable();
            $t->text('keterangan')->nullable();
            $t->string('status', 20)->default('Pending');
            $t->timestamps();
            $t->foreign('karyawan_id_karyawan')
                ->references('id_karyawan')->on('karyawans')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_izin');
    }
};
