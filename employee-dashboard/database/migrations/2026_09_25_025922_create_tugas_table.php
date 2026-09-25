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
        Schema::create('tugas', function (Blueprint $table) {
            $table->string('id_tugas', 20)->primary();

            $table->string('karyawan_id_karyawan', 20);

            $table->string('judul_tugas', 200);
            $table->text('deskripsi')->nullable();
            $table->date('deadline')->nullable();
            $table->string('progress', 20)->nullable();
            $table->string('status', 30);
            $table->date('tanggal_dibuat');
            $table->date('tanggal_update')->nullable();

            $table->foreign('karyawan_id_karyawan')
                ->references('id_karyawan')
                ->on('karyawans')
                ->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tugas');
    }
};
