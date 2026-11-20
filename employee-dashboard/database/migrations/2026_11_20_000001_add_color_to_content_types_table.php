<?php

use App\Models\ContentType;
use App\Support\ContentTypePalette;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_types', function (Blueprint $table) {
            $table->string('color', 20)->nullable()->after('name');
        });

        // Keep the look of the existing seeded types: their previous colour
        // came from the palette position, which is now stored on the row.
        ContentType::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->each(function (ContentType $type, int $index) {
                $colors = ContentTypePalette::COLORS;

                $type->update([
                    'color' => $colors[$index % count($colors)]['border'],
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('content_types', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
