<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_notes', function (Blueprint $table): void {
            // Timestamp email pengingat catatan yang terakhir dikirim.
            // Null berarti pengingat untuk tanggal ini belum pernah dikirim.
            $table->timestamp('reminder_email_sent_at')->nullable()->after('reminder_days_before');
        });
    }

    public function down(): void
    {
        Schema::table('calendar_notes', function (Blueprint $table): void {
            $table->dropColumn('reminder_email_sent_at');
        });
    }
};
