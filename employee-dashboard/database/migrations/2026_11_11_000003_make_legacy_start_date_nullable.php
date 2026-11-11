<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The calendar no longer writes the legacy multi-day columns. They stay in
     * the table for history, but new rows must not be forced to fill them.
     */
    public function up(): void
    {
        Schema::table('content_events', function (Blueprint $table) {
            $table->date('start_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('content_events', function (Blueprint $table) {
            $table->date('start_date')->nullable(false)->change();
        });
    }
};
