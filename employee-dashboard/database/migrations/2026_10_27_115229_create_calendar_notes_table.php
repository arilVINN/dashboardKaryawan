<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_id_user', 10);   // FK ke users2.id_user
            $table->date('tanggal');               // Tanggal note
            $table->string('judul', 255);          // Judul/isi singkat note
            $table->text('detail')->nullable();    // Detail tambahan (opsional)
            $table->string('warna', 20)->default('slate'); // slate | blue | green | amber | red | purple
            $table->timestamps();

            $table->foreign('user_id_user')
                ->references('id_user')
                ->on('users2')
                ->onDelete('cascade');

            $table->index(['user_id_user', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_notes');
    }
};
