<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawans', function (Blueprint $table): void {
            $table->string('status_aktif', 20)->default('Aktif')->after('jabatan');
        });
    }

    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table): void {
            $table->dropColumn('status_aktif');
        });
    }
};
