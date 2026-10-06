<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'slug' => str($name)->slug()->toString(),
            'icon' => 'code',
            'title' => ['en' => ucfirst($name), 'ar' => 'خدمة '.$name],
            'card_summary' => ['en' => 'Short summary.', 'ar' => 'ملخص قصير.'],
            'headline' => ['en' => 'Headline for '.$name, 'ar' => 'عنوان '.$name],
            'intro' => ['en' => 'Intro paragraph.', 'ar' => 'فقرة تمهيدية.'],
            'problem' => ['en' => '<p>The problem.</p>', 'ar' => '<p>المشكلة.</p>'],
            'is_published' => true,
            'sort_order' => fake()->numberBetween(1, 50),
        ];
    }
}
