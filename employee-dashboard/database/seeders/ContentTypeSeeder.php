<?php

namespace Database\Seeders;

use App\Models\ContentType;
use Illuminate\Database\Seeder;

class ContentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['slug' => 'reels', 'name' => 'Reels', 'color' => '#9DB9F5', 'sort_order' => 1],
            ['slug' => 'single-image', 'name' => 'Single Image', 'color' => '#F3C581', 'sort_order' => 2],
            ['slug' => 'carousel', 'name' => 'Carousel', 'color' => '#F3A8C9', 'sort_order' => 3],
        ];

        foreach ($types as $type) {
            ContentType::updateOrCreate(['slug' => $type['slug']], $type);
        }
    }
}
