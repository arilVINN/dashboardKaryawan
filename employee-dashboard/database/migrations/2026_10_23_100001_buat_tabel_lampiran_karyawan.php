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
        Schema::create('lampiran_karyawan', function (Blueprint $table) {
            $table->string('id_lampiran', 50)->primary();
            $table->string('karyawan_id_karyawan', 20);
            $table->string('jenis_lampiran', 50);
            $table->string('nama_file');
            $table->string('path_file');
            $table->timestamps();

            $table->foreign('karyawan_id_karyawan')
                ->references('id_karyawan')
                ->on('karyawans')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lampiran_karyawan');
    }
};

