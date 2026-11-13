<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_events', function (Blueprint $table) {
            $table->foreignId('content_account_id')
                ->nullable()
                ->after('content_type')
                ->constrained('content_accounts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('content_events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('content_account_id');
        });
    }
};
