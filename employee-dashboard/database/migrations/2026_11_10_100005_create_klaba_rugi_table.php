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
        Schema::create('klaba_rugi', function (Blueprint $table) {
            $table->string('id_klr', 8)->primary();
            $table->string('periode', 15); // Format: YYYY-MM
            $table->unsignedTinyInteger('minggu_ke'); // 1-5
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->decimal('total_pendapatan', 15, 2)->default(0);
            $table->decimal('total_pengeluaran', 15, 2)->default(0);
            $table->decimal('laba_bersih', 15, 2)->default(0);
            $table->decimal('margin_persen', 5, 2)->default(0);
            $table->string('bukti_laporan', 255)->nullable();
            $table->string('catatan', 500)->nullable();
            $table->string('status_verifikasi', 20)->default('menunggu'); // menunggu, disetujui, perlu_revisi
            $table->string('catatan_verifikasi', 500)->nullable();
            $table->date('tanggal_kirim');
            $table->string('divisi_id_divisi', 20);
            $table->string('user_id_user', 20);
            $table->string('diverifikasi_oleh_id_user', 20)->nullable();
            $table->timestamps();

            $table->foreign('divisi_id_divisi')
                ->references('id_divisi')
                ->on('divisis')
                ->onDelete('restrict');

            $table->foreign('user_id_user')
                ->references('id_user')
                ->on('users2')
                ->onDelete('restrict');

            $table->foreign('diverifikasi_oleh_id_user')
                ->references('id_user')
                ->on('users2')
                ->onDelete('set null');

            $table->unique(['divisi_id_divisi', 'periode', 'minggu_ke'], 'klaba_rugi_divisi_periode_minggu_unique');
            $table->index(['divisi_id_divisi', 'periode', 'status_verifikasi'], 'klaba_rugi_divisi_periode_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('klaba_rugi');
    }
};
