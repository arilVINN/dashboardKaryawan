<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesans', function (Blueprint $table): void {
            $table->string('status', 20)->default('belum_dibaca')->after('tipe');
        });
    }

    public function down(): void
    {
        Schema::table('pesans', function (Blueprint $table): void {
            $table->dropColumn('status');
        });
    }
};
