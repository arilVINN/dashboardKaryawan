<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_notes', function (Blueprint $table) {
            // Berapa hari sebelum tanggal note pengingat ditampilkan.
            // 0 = hari H, null = tanpa pengingat.
            // Default 1 agar note lama tetap berperilaku seperti sebelumnya.
            $table->unsignedSmallInteger('reminder_days_before')
                ->nullable()
                ->default(1)
                ->after('warna');
        });
    }

    public function down(): void
    {
        Schema::table('calendar_notes', function (Blueprint $table) {
            $table->dropColumn('reminder_days_before');
        });
    }
};
