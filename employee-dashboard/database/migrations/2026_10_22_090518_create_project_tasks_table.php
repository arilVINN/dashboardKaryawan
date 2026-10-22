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
        Schema::create('project_tasks', function (Blueprint $t) {
            $t->string('id_task', 15)->primary();
            $t->string('project_id_project', 15);
            $t->string('label', 200);
            $t->string('karyawan_id_karyawan', 10); // 1 task = 1 penerima
            $t->boolean('selesai')->default(false);
            $t->integer('urutan')->default(0);
            $t->timestamps();

            $t->foreign('project_id_project')->references('id_project')->on('projects')->cascadeOnDelete();
            $t->foreign('karyawan_id_karyawan')->references('id_karyawan')->on('karyawans');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_tasks');
    }
};
