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
        Schema::create('keuangan_aldiv', function (Blueprint $table) {
            $table->string('id_kad', 8)->primary();
            $table->string('periode', 15); // Format: YYYY-MM
            $table->decimal('nominal_alokasi', 15, 2);
            $table->string('keterangan', 250)->nullable();
            $table->string('divisi_id_divisi', 20);
            $table->string('user_id_user', 20);
            $table->timestamps();

            $table->foreign('divisi_id_divisi')
                ->references('id_divisi')
                ->on('divisis')
                ->onDelete('restrict');

            $table->foreign('user_id_user')
                ->references('id_user')
                ->on('users2')
                ->onDelete('restrict');

            $table->index(['divisi_id_divisi', 'periode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keuangan_aldiv');
    }
};
