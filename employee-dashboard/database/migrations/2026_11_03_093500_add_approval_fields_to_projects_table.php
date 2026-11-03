<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dateTime('approved_at')->nullable()->after('tenggat');
            $table->string('approved_by_id_user', 15)->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropColumn(['approved_at', 'approved_by_id_user']);
        });
    }
};
