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
        Schema::create('users2', function (Blueprint $table) {
            $table->string('id_user', 20)->primary();
            $table->string('username', 50)->unique();
            $table->string('password');

            $table->string('role_id_role', 20);
            $table->string('karyawan_id_karyawan', 20)->unique();

            $table->foreign('role_id_role')
                ->references('id_role')
                ->on('roles')
                ->onDelete('restrict');

            $table->foreign('karyawan_id_karyawan')
                ->references('id_karyawan')
                ->on('karyawans')
                ->onDelete('restrict');

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users2');
    }
};
