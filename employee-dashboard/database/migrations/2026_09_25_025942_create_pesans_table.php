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
        Schema::create('pesans', function (Blueprint $table) {
            $table->string('id_pesan', 20)->primary();

            $table->string('judul_pesan', 200);
            $table->text('deskripsi')->nullable();
            $table->date('tanggal_pesan');

            $table->string('tugas_id_tugas', 20);
            $table->string('tugas_karyawan_id_karyawan', 20);

            $table->foreign('tugas_id_tugas')
                ->references('id_tugas')
                ->on('tugas')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesans');
    }
};
