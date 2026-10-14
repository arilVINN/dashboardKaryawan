<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Read tracking for the recipient of a message. `null` means the message
     * is still unread; a timestamp is written the first time the recipient
     * opens it from the Daftar Pesan list or detail page.
     */
    public function up(): void
    {
        Schema::table('pesans', function (Blueprint $table): void {
            $table->timestamp('dibaca_at')->nullable()->after('balasan_dari_id_pesan');
        });
    }

    public function down(): void
    {
        Schema::table('pesans', function (Blueprint $table): void {
            $table->dropColumn('dibaca_at');
        });
    }
};
