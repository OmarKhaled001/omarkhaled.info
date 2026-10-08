<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** D-044: structured case-study sections — goals, audience and the user journey (translatable HTML). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->json('goals')->nullable()->after('challenge');
            $table->json('audience')->nullable()->after('goals');
            $table->json('journey')->nullable()->after('solution');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropColumn(['goals', 'audience', 'journey']);
        });
    }
};
