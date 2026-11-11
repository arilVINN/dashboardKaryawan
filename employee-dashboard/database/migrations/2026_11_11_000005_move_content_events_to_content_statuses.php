<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Content events moved from the task-style status list to a content
     * workflow. Existing values are remapped so nothing falls back to grey.
     */
    public function up(): void
    {
        $map = [
            'baru' => 'process',
            'berjalan' => 'process',
            'menunggu' => 'revisi',
            'disetujui' => 'approved',
            'terlambat' => 'cancel',
        ];

        foreach ($map as $from => $to) {
            DB::table('content_events')
                ->where('status', $from)
                ->update(['status' => $to]);
        }

        Schema::table('content_events', function (Blueprint $table) {
            $table->string('status', 30)->default('process')->change();
        });
    }

    public function down(): void
    {
        Schema::table('content_events', function (Blueprint $table) {
            $table->string('status', 30)->default('baru')->change();
        });
    }
};
