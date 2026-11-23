<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawans', function (Blueprint $table): void {
            $table->index('nama', 'karyawans_nama_index');
        });
    }

    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table): void {
            $table->dropIndex('karyawans_nama_index');
        });
    }
};
