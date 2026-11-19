<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnal_entries', function (Blueprint $table) {
            $table->id();
            $table->date('entry_date');
            $table->string('ref_code', 30)->unique();
            $table->string('source_type', 120)->nullable();
            $table->string('source_id', 50)->nullable();
            $table->unsignedBigInteger('jurnal_divisi_id')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('created_by', 50)->nullable();
            $table->timestamps();

            $table->index('entry_date');
            $table->index('jurnal_divisi_id');
            $table->index('ref_code');
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal_entries');
    }
};
