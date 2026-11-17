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
        Schema::table('daily_journals', function (Blueprint $table) {
            $table->string('file_bukti')->nullable()->after('catatan');
            $table->string('file_nama_asli')->nullable()->after('file_bukti');
            $table->text('link_bukti')->nullable()->after('file_nama_asli');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_journals', function (Blueprint $table) {
            $table->dropColumn(['file_bukti', 'file_nama_asli', 'link_bukti']);
        });
    }
};
