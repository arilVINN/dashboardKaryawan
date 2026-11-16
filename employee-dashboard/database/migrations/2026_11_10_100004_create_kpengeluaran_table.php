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
        Schema::create('kpengeluaran', function (Blueprint $table) {
            $table->string('id_kpengeluaran', 8)->primary();
            $table->string('kode_pengeluaran', 15)->unique();
            $table->string('kpd_id_kpd', 8)->nullable();
            $table->decimal('nominal', 15, 2);
            $table->date('tanggal_cair');
            $table->string('metode_pembayaran', 25); // Transfer Bank, Kas Kecil, Talangan Direktur
            $table->string('bukti_pencairan', 255)->nullable();
            $table->string('keterangan', 500)->nullable();
            $table->string('divisi_id_divisi', 20);
            $table->string('user_id_user', 20);
            $table->timestamps();

            $table->foreign('kpd_id_kpd')
                ->references('id_kpd')
                ->on('kpermint_dana')
                ->onDelete('set null');

            $table->foreign('divisi_id_divisi')
                ->references('id_divisi')
                ->on('divisis')
                ->onDelete('restrict');

            $table->foreign('user_id_user')
                ->references('id_user')
                ->on('users2')
                ->onDelete('restrict');

            $table->index(['divisi_id_divisi', 'tanggal_cair']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpengeluaran');
    }
};
