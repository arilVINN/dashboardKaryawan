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
        Schema::create('notifikasis', function (Blueprint $table) {
            $table->string('id_notifikasi', 20)->primary();

            $table->string('judul_notifikasi', 200);
            $table->text('isi_notif');
            $table->date('tanggal_notifikasi');

            $table->string('user_id_user', 20);

            $table->foreign('user_id_user')
                ->references('id_user')
                ->on('users2')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifikasis');
    }
};
