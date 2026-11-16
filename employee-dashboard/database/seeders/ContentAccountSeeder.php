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
            ['slug' => 'silindo-official', 'name' => 'Silindo Official', 'handle' => '@silindo.official', 'platform' => 'Instagram', 'color' => '#E1306C'],
            ['slug' => 'silindo-karier', 'name' => 'Silindo Karier', 'handle' => '@silindo.karier', 'platform' => 'Instagram', 'color' => '#2563EB'],
            ['slug' => 'silindo-produk', 'name' => 'Silindo Produk', 'handle' => '@silindo.produk', 'platform' => 'Instagram', 'color' => '#059669'],
            ['slug' => 'silindo-edukasi', 'name' => 'Silindo Edukasi', 'handle' => '@silindo.edu', 'platform' => 'Instagram', 'color' => '#D97706'],
            ['slug' => 'silindo-event', 'name' => 'Silindo Event', 'handle' => '@silindo.event', 'platform' => 'Instagram', 'color' => '#7C3AED'],
            ['slug' => 'silindo-reels', 'name' => 'Silindo Reels', 'handle' => '@silindo.reels', 'platform' => 'Instagram', 'color' => '#DB2777'],
            ['slug' => 'silindo-stories', 'name' => 'Silindo Stories', 'handle' => '@silindo.story', 'platform' => 'Instagram', 'color' => '#0891B2'],
            ['slug' => 'silindo-tiktok', 'name' => 'Silindo TikTok', 'handle' => '@silindo.id', 'platform' => 'TikTok', 'color' => '#111827'],
            ['slug' => 'silindo-partner', 'name' => 'Silindo Partner', 'handle' => '@silindo.partner', 'platform' => 'TikTok', 'color' => '#4F46E5'],
            ['slug' => 'silindo-shorts', 'name' => 'Silindo Shorts', 'handle' => '@silindotv', 'platform' => 'YouTube', 'color' => '#DC2626'],
            ['slug' => 'silindo-linkedin', 'name' => 'Silindo LinkedIn', 'handle' => '/company/silindo', 'platform' => 'LinkedIn', 'color' => '#0A66C2'],
            ['slug' => 'silindo-facebook', 'name' => 'Silindo Facebook', 'handle' => '/silindo.id', 'platform' => 'Facebook', 'color' => '#1877F2'],
            ['slug' => 'silindo-x', 'name' => 'Silindo X', 'handle' => '@silindo_id', 'platform' => 'X', 'color' => '#334155'],
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

        // Keep events created before accounts existed visible on the default
        // account calendar instead of hiding them behind a null account.
        $default = ContentAccount::query()->ordered()->first();

        if ($default) {
            ContentEvent::query()
                ->whereNull('content_account_id')
                ->update(['content_account_id' => $default->id]);
        }
    }
}
