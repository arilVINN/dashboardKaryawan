<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('project_tasks');
        Schema::dropIfExists('projects');
    }

    public function down(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->string('id_project', 15)->primary();
            $table->string('nama_project', 150);
            $table->text('deskripsi')->nullable();
            $table->string('dibuat_oleh_id_user', 15);
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->dateTime('tenggat')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->string('approved_by_id_user', 15)->nullable();
            $table->timestamps();
        });

        Schema::create('project_tasks', function (Blueprint $table): void {
            $table->string('id_task', 15)->primary();
            $table->string('project_id_project', 15);
            $table->string('label', 200);
            $table->string('karyawan_id_karyawan', 10);
            $table->boolean('selesai')->default(false);
            $table->integer('urutan')->default(0);
            $table->string('file_hasil_path')->nullable();
            $table->string('file_hasil_name')->nullable();
            $table->timestamp('tanggal_submit')->nullable();
            $table->timestamps();

            $table->foreign('project_id_project')
                ->references('id_project')
                ->on('projects')
                ->cascadeOnDelete();
            $table->foreign('karyawan_id_karyawan')
                ->references('id_karyawan')
                ->on('karyawans');
        });
    }
};
