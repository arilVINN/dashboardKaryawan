<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesans', function (Blueprint $table): void {
            $table->string('balasan_dari_id_pesan', 20)->nullable()->after('penerima_id_user');
            $table->foreign('balasan_dari_id_pesan')
                ->references('id_pesan')
                ->on('pesans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pesans', function (Blueprint $table): void {
            $table->dropForeign(['balasan_dari_id_pesan']);
            $table->dropColumn('balasan_dari_id_pesan');
        });
    }
};