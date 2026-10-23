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
            $t->string('pendidikan', 50)->nullable();
            $t->string('job_level', 50)->nullable();
            $t->string('status_karyawan', 20)->default('tetap');
            $t->boolean('aktif')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $t) {
            $t->dropColumn(['pendidikan', 'job_level', 'status_karyawan', 'aktif']);
        });
    }
};
