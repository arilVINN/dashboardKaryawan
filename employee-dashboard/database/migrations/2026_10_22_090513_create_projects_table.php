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
        Schema::create('projects', function (Blueprint $t) {
            $t->string('id_project', 15)->primary();
            $t->string('nama_project', 150);
            $t->text('deskripsi')->nullable();
            $t->string('dibuat_oleh_id_user', 15); // kadiv yang bikin
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
