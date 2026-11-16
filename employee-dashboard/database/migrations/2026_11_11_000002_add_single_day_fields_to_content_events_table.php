<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The calendar moved to one date and one posting time per event. The old
     * multi-day, colour, division and PIC columns stay in place (nullable and
     * unused) so nothing is lost, but all reads move to the new columns.
     */
    public function up(): void
    {
        Schema::table('content_events', function (Blueprint $table) {
            $table->date('event_date')->nullable()->after('title');
            $table->time('post_time')->nullable()->after('event_date');
            $table->string('content_type', 50)->nullable()->after('post_time');
            $table->string('content_url', 2048)->nullable()->after('description');

            $table->index('event_date');
        });

        // Backfill: keep the start date of multi-day events, drop the span.
        DB::table('content_events')->update([
            'event_date' => DB::raw('start_date'),
        ]);

        DB::table('content_events')
            ->whereNotNull('start_time')
            ->update(['post_time' => DB::raw('start_time')]);

        DB::table('content_events')->update(['content_type' => 'reels']);
    }

    public function down(): void
    {
        Schema::table('content_events', function (Blueprint $table) {
            $table->dropIndex(['event_date']);
            $table->dropColumn(['event_date', 'post_time', 'content_type', 'content_url']);
        });
    }
};
