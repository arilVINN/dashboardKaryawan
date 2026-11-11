<?php

namespace Database\Seeders;

use App\Models\ContentType;
use Illuminate\Database\Seeder;

class ContentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['slug' => 'reels', 'name' => 'Reels', 'sort_order' => 1],
            ['slug' => 'single-image', 'name' => 'Single Image', 'sort_order' => 2],
            ['slug' => 'carousel', 'name' => 'Carousel', 'sort_order' => 3],
        ];

        foreach ($types as $type) {
            ContentType::updateOrCreate(['slug' => $type['slug']], $type);
        }
    }
}
