<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Replace the original placeholder accounts with the brand account list.
     * Old accounts are deactivated instead of deleted so no history is lost,
     * and their events move to the default PT SILINDO calendar.
     */
    public function up(): void
    {
        $now = now();

        $accounts = [
            ['slug' => 'pt-silindo', 'name' => 'PT SILINDO', 'sort_order' => 1],
            ['slug' => 'penerbit-buku', 'name' => 'Penerbit Buku', 'sort_order' => 2],
            ['slug' => 'academic-events', 'name' => 'Academic Events', 'sort_order' => 3],
            ['slug' => 'publikasi-jurnal', 'name' => 'Publikasi Jurnal', 'sort_order' => 4],
            ['slug' => 'citra-komunika', 'name' => 'Citra Komunika', 'sort_order' => 5],
            ['slug' => 'think-with-meida', 'name' => 'Think With Meida', 'sort_order' => 6],
            ['slug' => 'meira-kost', 'name' => 'MeiRa Kost', 'sort_order' => 7],
        ];

        $rows = array_map(fn (array $account) => $account + [
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ], $accounts);

        DB::table('content_accounts')->upsert(
            $rows,
            ['slug'],
            ['name', 'sort_order', 'is_active', 'updated_at']
        );

        $defaultId = DB::table('content_accounts')
            ->where('slug', 'pt-silindo')
            ->value('id');

        if (! $defaultId) {
            return;
        }

        $oldSlugs = [
            'silindo-official',
            'silindo-karier',
            'silindo-produk',
            'silindo-edukasi',
            'silindo-event',
            'silindo-reels',
            'silindo-stories',
            'silindo-tiktok',
            'silindo-partner',
            'silindo-shorts',
            'silindo-linkedin',
            'silindo-facebook',
            'silindo-x',
        ];

        $oldIds = DB::table('content_accounts')->whereIn('slug', $oldSlugs)->pluck('id');

        if ($oldIds->isNotEmpty()) {
            DB::table('content_events')
                ->whereIn('content_account_id', $oldIds)
                ->update([
                    'content_account_id' => $defaultId,
                    'updated_at' => $now,
                ]);
        }

        // Events created before accounts existed stay visible on the default.
        DB::table('content_events')
            ->whereNull('content_account_id')
            ->update([
                'content_account_id' => $defaultId,
                'updated_at' => $now,
            ]);

        DB::table('content_accounts')
            ->whereIn('slug', $oldSlugs)
            ->update([
                'is_active' => false,
                'updated_at' => $now,
            ]);
    }

    /**
     * Reverse the activation flags. Event ownership is intentionally not moved
     * back because the old distribution cannot be reconstructed reliably.
     */
    public function down(): void
    {
        $newSlugs = [
            'pt-silindo',
            'penerbit-buku',
            'academic-events',
            'publikasi-jurnal',
            'citra-komunika',
            'think-with-meida',
            'meira-kost',
        ];

        $oldSlugs = [
            'silindo-official',
            'silindo-karier',
            'silindo-produk',
            'silindo-edukasi',
            'silindo-event',
            'silindo-reels',
            'silindo-stories',
            'silindo-tiktok',
            'silindo-partner',
            'silindo-shorts',
            'silindo-linkedin',
            'silindo-facebook',
            'silindo-x',
        ];

        DB::table('content_accounts')->whereIn('slug', $oldSlugs)->update(['is_active' => true]);
        DB::table('content_accounts')->whereIn('slug', $newSlugs)->update(['is_active' => false]);
    }
};
