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
        Schema::create('karyawans', function (Blueprint $table) {
            $table->string('id_karyawan', 20)->primary();
            $table->string('nama', 100);
            $table->string('jenis_kelamin', 20);
            $table->date('tanggal_lahir')->nullable();
            $table->date('tanggal_rekrut')->nullable();
            $table->string('no_telepon', 20)->nullable();
            $table->string('email', 100)->unique();
            $table->string('jabatan', 100);
            
            $table->string('divisi_id_divisi', 20);

            $table->foreign('divisi_id_divisi')
                ->references('id_divisi')
                ->on('divisis')
                ->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};
