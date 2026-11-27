<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('carousels', function (Blueprint $table) {
            if (! Schema::hasColumn('carousels', 'subtitle')) {
                $table->string('subtitle')->nullable()->after('title');
            }
            if (! Schema::hasColumn('carousels', 'description')) {
                $table->text('description')->nullable()->after('subtitle');
            }
            if (! Schema::hasColumn('carousels', 'initial_likes')) {
                $table->unsignedInteger('initial_likes')->default(0)->after('image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carousels', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('carousels', 'subtitle')) {
                $columnsToDrop[] = 'subtitle';
            }
            if (Schema::hasColumn('carousels', 'description')) {
                $columnsToDrop[] = 'description';
            }
            if (Schema::hasColumn('carousels', 'initial_likes')) {
                $columnsToDrop[] = 'initial_likes';
            }
            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
