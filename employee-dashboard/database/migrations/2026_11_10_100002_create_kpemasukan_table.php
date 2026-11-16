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
        Schema::create('kpemasukan', function (Blueprint $table) {
            $table->string('id_kp', 8)->primary();
            $table->string('kode_transaksi', 8)->unique();
            $table->string('kategori_pemasukan', 25);
            $table->decimal('nominal', 15, 2);
            $table->date('tanggal_masuk');
            $table->string('keterangan', 500)->nullable();
            $table->string('bukti_dokumen', 255)->nullable();
            $table->string('divisi_id_divisi', 20)->nullable();
            $table->string('user_id_user', 20);
            $table->timestamps();

            $table->foreign('divisi_id_divisi')
                ->references('id_divisi')
                ->on('divisis')
                ->onDelete('set null');

            $table->foreign('user_id_user')
                ->references('id_user')
                ->on('users2')
                ->onDelete('restrict');

            $table->index(['divisi_id_divisi', 'tanggal_masuk']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpemasukan');
    }
};
