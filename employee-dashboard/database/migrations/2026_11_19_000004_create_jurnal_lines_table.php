<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnal_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jurnal_entry_id');
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('debit')->default(0);
            $table->unsignedBigInteger('credit')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('jurnal_entry_id')
                ->references('id')->on('jurnal_entries')->onDelete('cascade');
            $table->foreign('account_id')
                ->references('id')->on('coa_akuns')->onDelete('restrict');

            $table->index(['jurnal_entry_id', 'sort_order']);
            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal_lines');
    }
};
