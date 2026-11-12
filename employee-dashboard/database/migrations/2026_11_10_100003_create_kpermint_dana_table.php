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
        Schema::create('kpermint_dana', function (Blueprint $table) {
            $table->string('id_kpd', 8)->primary();
            $table->string('nomor_pengajuan', 15)->unique();
            $table->string('judul_pengajuan', 250);
            $table->string('keperluan', 250);
            $table->decimal('nominal_diajukan', 15, 2);
            $table->string('lampiran_proposal', 255)->nullable();
            $table->string('status', 15)->default('menunggu'); // menunggu, disetujui, ditolak, dicairkan
            $table->string('catatan_finance', 500)->nullable();
            $table->date('tanggal_disetujui')->nullable();
            $table->string('divisi_id_divisi', 20);
            $table->string('user_id_user', 20);
            $table->string('disetujui_oleh_id_user', 20)->nullable();
            $table->timestamps();

            $table->foreign('divisi_id_divisi')
                ->references('id_divisi')
                ->on('divisis')
                ->onDelete('restrict');

            $table->foreign('user_id_user')
                ->references('id_user')
                ->on('users2')
                ->onDelete('restrict');

            $table->foreign('disetujui_oleh_id_user')
                ->references('id_user')
                ->on('users2')
                ->onDelete('set null');

            $table->index(['divisi_id_divisi', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpermint_dana');
    }
};
