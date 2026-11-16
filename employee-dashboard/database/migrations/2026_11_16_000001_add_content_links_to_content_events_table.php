<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_events', function (Blueprint $table) {
            $table->json('content_links')->nullable()->after('content_url');
        });

        // Keep the existing single link as the first attachment.
        DB::table('content_events')
            ->whereNotNull('content_url')
            ->where('content_url', '!=', '')
            ->select(['id', 'content_url'])
            ->orderBy('id')
            ->get()
            ->each(function ($event) {
                DB::table('content_events')
                    ->where('id', $event->id)
                    ->update(['content_links' => json_encode([$event->content_url])]);
            });
    }

    public function down(): void
    {
        Schema::table('content_events', function (Blueprint $table) {
            $table->dropColumn('content_links');
        });
    }
};
