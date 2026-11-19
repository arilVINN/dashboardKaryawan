<?php

namespace Database\Seeders;

use App\Models\ContentPillar;
use Illuminate\Database\Seeder;

class ContentPillarSeeder extends Seeder
{
    public function run(): void
    {
        $pillars = [
            ['slug' => 'informasi', 'name' => 'Informasi', 'color' => '#2563EB', 'sort_order' => 1],
            ['slug' => 'edukasi', 'name' => 'Edukasi', 'color' => '#059669', 'sort_order' => 2],
            ['slug' => 'entertain', 'name' => 'Entertain', 'color' => '#7C3AED', 'sort_order' => 3],
            ['slug' => 'promosi', 'name' => 'Promosi', 'color' => '#EA580C', 'sort_order' => 4],
        ];

        foreach ($pillars as $pillar) {
            ContentPillar::updateOrCreate(['slug' => $pillar['slug']], $pillar);
        }
    }
}
