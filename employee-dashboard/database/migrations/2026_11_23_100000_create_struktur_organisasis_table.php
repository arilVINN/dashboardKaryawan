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
        Schema::create('struktur_organisasis', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jabatan');
            $table->string('divisi')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('urutan')->default(1);
            $table->integer('level')->default(1);
            $table->string('foto')->nullable();
            $table->string('email')->nullable();
            $table->string('no_telepon')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('id_karyawan', 20)->nullable();
            $table->timestamps();

            $table->foreign('parent_id')
                ->references('id')
                ->on('struktur_organisasis')
                ->nullOnDelete();

            $table->foreign('id_karyawan')
                ->references('id_karyawan')
                ->on('karyawans')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('struktur_organisasis');
    }
};
