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
        Schema::create('carousels', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category', 50)->default('Event');
            $table->date('date')->nullable();
            $table->string('image');
            $table->string('user_id_user', 20)->nullable();
            $table->string('divisi_id_divisi', 20)->nullable();
            $table->timestamps();

            $table->foreign('user_id_user')
                ->references('id_user')
                ->on('users2')
                ->nullOnDelete();

            $table->foreign('divisi_id_divisi')
                ->references('id_divisi')
                ->on('divisis')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carousels');
    }
};
