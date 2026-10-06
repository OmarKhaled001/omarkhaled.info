<?php

namespace Database\Factories;

use App\Enums\EngagementType;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $client = 'Acme '.fake()->unique()->lexify('????').' Trading';

        return [
            'slug' => fake()->unique()->slug(3),
            'title' => ['en' => "{$client} platform", 'ar' => "منصة {$client}"],
            'anonymized_title' => ['en' => 'Platform for a trading company', 'ar' => 'منصة لشركة تجارية'],
            'summary' => ['en' => "Ordering platform built for {$client}.", 'ar' => "منصة طلبات لشركة {$client}."],
            'anonymized_summary' => ['en' => 'Ordering platform for a trading company.', 'ar' => 'منصة طلبات لشركة تجارية.'],
            'client_name' => ['en' => $client, 'ar' => $client],
            'client_aliases' => [str_replace(' ', '', strtolower($client))],
            'industry' => ['en' => 'Trading', 'ar' => 'التجارة'],
            'role' => ['en' => 'Full-stack developer', 'ar' => 'مطوّر Full-Stack'],
            'challenge' => ['en' => '<p>The team needed one system.</p>', 'ar' => '<p>احتاج الفريق إلى نظام واحد.</p>'],
            'solution' => ['en' => '<p>A Laravel platform.</p>', 'ar' => '<p>منصة Laravel.</p>'],
            'engagement_type' => EngagementType::Client,
            'live_url' => 'https://'.str_replace(' ', '', strtolower($client)).'.example',
            'year' => 2026,
            'is_published' => true,
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }

    public function draft(): static
    {
        return $this->state(['is_published' => false]);
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }

    public function revealed(): static
    {
        return $this->state([
            'show_client_name' => true,
            'show_live_link' => true,
            'show_logo' => true,
            'show_screenshots' => true,
        ]);
    }
}
