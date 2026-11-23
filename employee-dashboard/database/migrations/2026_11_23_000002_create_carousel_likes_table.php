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
        if (!Schema::hasTable('carousel_likes')) {
            Schema::create('carousel_likes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('carousel_id')->constrained('carousels')->cascadeOnDelete();
                $table->string('user_id_user', 20);
                $table->timestamps();

                $table->foreign('user_id_user')
                    ->references('id_user')
                    ->on('users2')
                    ->cascadeOnDelete();

                $table->unique(['carousel_id', 'user_id_user']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carousel_likes');
    }
};
