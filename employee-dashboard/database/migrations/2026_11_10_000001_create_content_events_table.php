<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_events', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('division_id', 20)->nullable();
            $table->string('status', 30)->default('baru');
            $table->string('pic_user_id', 20)->nullable();
            $table->text('description')->nullable();
            $table->string('created_by', 20);
            $table->timestamps();

            $table->index('start_date');
            $table->index('end_date');
            $table->index('division_id');
            $table->index('status');

            $table->foreign('division_id')
                ->references('id_divisi')
                ->on('divisis')
                ->nullOnDelete();

            $table->foreign('pic_user_id')
                ->references('id_user')
                ->on('users2')
                ->nullOnDelete();

            $table->foreign('created_by')
                ->references('id_user')
                ->on('users2')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_events');
    }
};
