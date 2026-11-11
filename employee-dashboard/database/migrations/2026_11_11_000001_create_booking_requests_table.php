<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kategori', 20);
            $table->string('jenis', 20);
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('keperluan', 500);
            $table->string('status', 20)->default('Pending');
            $table->string('pemohon_id_user', 20);
            $table->string('diproses_oleh_id_user', 20)->nullable();
            $table->timestamp('diproses_pada')->nullable();
            $table->timestamps();

            $table->foreign('pemohon_id_user')->references('id_user')->on('users2')->cascadeOnDelete();
            $table->foreign('diproses_oleh_id_user')->references('id_user')->on('users2')->nullOnDelete();
            $table->index(['kategori', 'jenis', 'tanggal', 'status', 'jam_mulai', 'jam_selesai'], 'booking_schedule_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_requests');
    }
};
