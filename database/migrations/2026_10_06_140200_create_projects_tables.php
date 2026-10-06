<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();

            // Real vs anonymized identity (spatie/laravel-translatable JSON).
            $table->json('title');
            $table->json('anonymized_title');
            $table->json('summary');
            $table->json('anonymized_summary');
            $table->json('client_name')->nullable();
            $table->json('client_aliases')->nullable();

            $table->json('industry')->nullable();
            $table->json('role')->nullable();

            // Long-form case study fields — always written client-neutral.
            $table->json('challenge')->nullable();
            $table->json('solution')->nullable();
            $table->json('architecture')->nullable();
            $table->json('results')->nullable();

            $table->string('engagement_type', 16)->default('client');
            $table->string('schema_type', 32)->default('CreativeWork');
            $table->string('live_url')->nullable();
            $table->string('repo_url')->nullable();
            $table->unsignedSmallInteger('year')->nullable();

            $table->boolean('show_client_name')->default(false);
            $table->boolean('show_live_link')->default(false);
            $table->boolean('show_logo')->default(false);
            $table->boolean('show_screenshots')->default(false);
            $table->boolean('show_repo_link')->default(false);

            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
            $table->index(['is_published', 'is_featured', 'sort_order']);
        });

        Schema::create('project_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->json('title');
            $table->json('body')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['project_id', 'sort_order']);
        });

        Schema::create('project_facts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->json('label');
            $table->string('value', 64);
            $table->string('source_note')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['project_id', 'sort_order']);
        });

        Schema::create('category_project', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->primary(['category_id', 'project_id']);
            $table->index('project_id');
        });

        Schema::create('project_technology', function (Blueprint $table) {
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technology_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->primary(['project_id', 'technology_id']);
            $table->index('technology_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_technology');
        Schema::dropIfExists('category_project');
        Schema::dropIfExists('project_facts');
        Schema::dropIfExists('project_features');
        Schema::dropIfExists('projects');
    }
};
