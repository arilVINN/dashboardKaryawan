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
        Schema::table('karyawans', function (Blueprint $t) {
            $t->date('tanggal_keluar')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $t) {
            $t->dropColumn('tanggal_keluar');
        });
    }
};
