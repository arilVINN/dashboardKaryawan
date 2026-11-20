<?php

namespace Database\Seeders;

use App\Models\ContentAccount;
use App\Models\ContentEvent;
use Illuminate\Database\Seeder;

class ContentAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['slug' => 'pt-silindo', 'name' => 'PT SILINDO'],
            ['slug' => 'penerbit-buku', 'name' => 'Penerbit Buku'],
            ['slug' => 'academic-events', 'name' => 'Academic Events'],
            ['slug' => 'publikasi-jurnal', 'name' => 'Publikasi Jurnal'],
            ['slug' => 'citra-komunika', 'name' => 'Citra Komunika'],
            ['slug' => 'think-with-meida', 'name' => 'Think With Meida'],
            ['slug' => 'meira-kost', 'name' => 'MeiRa Kost'],
        ];

        foreach ($accounts as $index => $account) {
            ContentAccount::updateOrCreate(
                ['slug' => $account['slug']],
                $account + [
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }

        $default = ContentAccount::query()->active()->ordered()->first();

        if (! $default) {
            return;
        }

        // Retire the old social-media accounts, keeping their event history on
        // the default brand calendar instead of losing it.
        $legacySlugs = [
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

        $legacyIds = ContentAccount::query()
            ->whereIn('slug', $legacySlugs)
            ->pluck('id');

        if ($legacyIds->isNotEmpty()) {
            ContentEvent::query()
                ->whereIn('content_account_id', $legacyIds)
                ->update(['content_account_id' => $default->id]);

            ContentAccount::query()
                ->whereIn('id', $legacyIds)
                ->update(['is_active' => false]);
        }

        // Events created before accounts existed stay visible on the default.
        ContentEvent::query()
            ->whereNull('content_account_id')
            ->update(['content_account_id' => $default->id]);
    }
}
