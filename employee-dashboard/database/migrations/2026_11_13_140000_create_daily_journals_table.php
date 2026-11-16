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
        Schema::create('daily_journals', function (Blueprint $table) {
            $table->id();
            $table->string('user_id_user', 20);
            $table->string('karyawan_id_karyawan', 20)->nullable();
            $table->date('tanggal');
            $table->text('aktivitas');
            $table->string('prioritas', 20)->default('sedang'); // rendah | sedang | tinggi
            $table->string('status', 20)->default('on_progress'); // on_progress | selesai
            $table->text('catatan')->nullable(); // bukti pengerjaan / keterangan catatan (opsional)
            $table->timestamps();

            $table->foreign('user_id_user')
                ->references('id_user')
                ->on('users2')
                ->onDelete('cascade');

            $table->foreign('karyawan_id_karyawan')
                ->references('id_karyawan')
                ->on('karyawans')
                ->onDelete('set null');

            $table->index(['user_id_user', 'tanggal']);
            $table->index(['karyawan_id_karyawan', 'tanggal']);
            $table->index('tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_journals');
    }
};
