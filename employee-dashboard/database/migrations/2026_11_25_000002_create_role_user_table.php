<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Additional roles for a user. The primary role stays on users2 and still
     * follows the jabatan mapping; these are additive and grant full access.
     */
    public function up(): void
    {
        Schema::create('role_user', function (Blueprint $table): void {
            $table->string('id_user', 20);
            $table->string('id_role', 20);
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('users2')->cascadeOnDelete();
            $table->foreign('id_role')->references('id_role')->on('roles')->cascadeOnDelete();
            $table->unique(['id_user', 'id_role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
    }
};
