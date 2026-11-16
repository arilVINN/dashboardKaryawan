<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->string('file_hasil_path')->nullable();
            $table->string('file_hasil_name')->nullable();
            $table->timestamp('tanggal_submit')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropColumn(['file_hasil_path', 'file_hasil_name', 'tanggal_submit']);
        });
    }
};
