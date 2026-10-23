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
        Schema::table('karyawans', function (Blueprint $table) {
            $table->string('no_rekening', 50)->nullable();
            $table->date('tanggal_akhir_kontrak')->nullable();
            $table->renameColumn('tanggal_rekrut', 'tanggal_masuk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->renameColumn('tanggal_masuk', 'tanggal_rekrut');
            $table->dropColumn(['no_rekening', 'tanggal_akhir_kontrak']);
        });
    }
};

