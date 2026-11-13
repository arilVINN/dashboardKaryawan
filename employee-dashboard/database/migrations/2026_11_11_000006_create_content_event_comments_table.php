<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_event_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_event_id')->constrained('content_events')->cascadeOnDelete();
            $table->string('user_id_user', 20);
            $table->text('body');
            $table->timestamps();

            $table->index('content_event_id');

            $table->foreign('user_id_user')
                ->references('id_user')
                ->on('users2')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_event_comments');
    }
};
