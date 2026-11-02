<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tugas', function (Blueprint $table): void {
            // Timestamp pengingat deadline 24 jam yang terakhir dikirim.
            // Null berarti pengingat belum pernah dikirim untuk deadline ini.
            $table->timestamp('deadline_reminder_sent_at')->nullable()->after('deadline');
        });
    }

    public function down(): void
    {
        Schema::table('tugas', function (Blueprint $table): void {
            $table->dropColumn('deadline_reminder_sent_at');
        });
    }
};
