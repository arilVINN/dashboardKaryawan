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
        Schema::create('submit_tugas', function (Blueprint $table) {
            $table->string('id_submit_tugas', 20)->primary();

            $table->text('link_submit')->nullable();
            $table->string('file_hasil')->nullable();

            $table->text('catatan_karyawan')->nullable();
            $table->text('catatan_revisi')->nullable();

            $table->date('tanggal_submit')->nullable();
            $table->string('status_review', 30);

            $table->string('tugas_id_tugas', 20);
            $table->string('tugas_karyawan_id_karyawan', 20);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submit_tugas');
    }
};
