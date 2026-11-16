<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('karyawan_id_karyawan', 20)
                ->nullable();

            $table->foreign('karyawan_id_karyawan')
                ->references('id_karyawan')
                ->on('karyawans')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['karyawan_id_karyawan']);
            $table->dropColumn('karyawan_id_karyawan');
        });
    }
};
