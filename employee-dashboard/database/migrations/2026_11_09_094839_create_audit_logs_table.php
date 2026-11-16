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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('user_id_user', 20)->nullable();
            $table->string('aksi', 100);
            $table->string('target_tipe', 100);
            $table->string('target_id', 50)->nullable();
            $table->json('detail')->nullable();
            $table->timestamps();

            $table->foreign('user_id_user')
                ->references('id_user')
                ->on('users2')
                ->nullOnDelete();

            $table->index(['aksi', 'target_tipe']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
