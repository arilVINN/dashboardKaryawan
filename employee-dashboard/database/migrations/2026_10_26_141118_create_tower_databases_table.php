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
        Schema::create('tower_databases', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 255);
            $table->text('keterangan')->nullable();
            $table->string('jenis', 30); // 'file', 'nomor', 'link'
            $table->string('file_path', 255)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->string('file_size', 50)->nullable();
            $table->string('file_ext', 20)->nullable();
            $table->string('nilai_kontak', 100)->nullable();
            $table->string('email_kontak', 100)->nullable();
            $table->text('url_link')->nullable();
            $table->string('dibuat_oleh_id_karyawan', 20)->nullable();
            $table->timestamps();

            $table->foreign('dibuat_oleh_id_karyawan')
                ->references('id_karyawan')
                ->on('karyawans')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tower_databases');
    }
};
