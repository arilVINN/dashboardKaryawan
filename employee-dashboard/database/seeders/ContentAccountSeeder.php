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

        // Keep events created before accounts existed visible on the default
        // account calendar instead of hiding them behind a null account.
        $default = ContentAccount::query()->active()->ordered()->first();

        if ($default) {
            ContentEvent::query()
                ->whereNull('content_account_id')
                ->update(['content_account_id' => $default->id]);
        }
    }
}
