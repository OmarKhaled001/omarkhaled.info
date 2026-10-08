<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Project;
use App\Models\Service;
use App\Models\Technology;
use Illuminate\Database\Seeder;

/**
 * Seeds every project ANONYMIZED (data/projects.php), then layers the owner-approved public
 * showcase (data/project-showcase.php): copy, extra sections, visibility toggles and cover mockups.
 */
class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $showcase = require __DIR__.'/data/project-showcase.php';

        foreach (require __DIR__.'/data/projects.php' as $data) {
            $data = array_replace($data, $showcase[$data['slug']] ?? []);
            $data['features'] = [...$data['features'], ...($data['extra_features'] ?? [])];
            $reveal = $data['reveal'] ?? [];

            $project = Project::query()->updateOrCreate(['slug' => $data['slug']], [
                'title' => $data['title'],
                'anonymized_title' => $data['anonymized_title'],
                'summary' => $data['summary'],
                'anonymized_summary' => $data['anonymized_summary'],
                'client_name' => $data['client_name'],
                'client_aliases' => $data['client_aliases'],
                'industry' => $data['industry'],
                'role' => $data['role'],
                'challenge' => $data['challenge'],
                'goals' => $data['goals'] ?? null,
                'audience' => $data['audience'] ?? null,
                'solution' => $data['solution'],
                'journey' => $data['journey'] ?? null,
                'architecture' => $data['architecture'],
                'results' => null,
                'engagement_type' => $data['engagement_type'],
                'schema_type' => $data['schema_type'],
                'live_url' => $data['live_url'],
                'repo_url' => $data['repo_url'] ?? null,
                'year' => $data['year'],
                'is_published' => $data['is_published'],
                'is_featured' => $data['is_featured'],
                'sort_order' => $data['sort_order'],
                'show_client_name' => $reveal['client_name'] ?? false,
                'show_live_link' => $reveal['live_link'] ?? false,
                'show_logo' => false,
                'show_screenshots' => false,
                'show_repo_link' => $reveal['repo_link'] ?? false,
            ]);

            $this->attachCover($project, $data['cover'] ?? null);

            $project->features()->delete();
            foreach ($data['features'] as $i => [$enTitle, $enBody, $arTitle, $arBody]) {
                $project->features()->create([
                    'title' => ['en' => $enTitle, 'ar' => $arTitle],
                    'body' => ['en' => $enBody, 'ar' => $arBody],
                    'sort_order' => $i,
                ]);
            }

            $project->facts()->delete();
            foreach ($data['facts'] as $i => [$value, $enLabel, $arLabel, $source]) {
                $project->facts()->create([
                    'value' => $value,
                    'label' => ['en' => $enLabel, 'ar' => $arLabel],
                    'source_note' => $source,
                    'sort_order' => $i,
                ]);
            }

            $project->categories()->sync(Category::query()->whereIn('slug', $data['categories'])->pluck('id'));

            $techIds = Technology::query()->whereIn('slug', $data['technologies'])->pluck('id', 'slug');
            $project->technologies()->sync(collect($data['technologies'])
                ->filter(fn (string $slug) => $techIds->has($slug))
                ->mapWithKeys(fn (string $slug, int $i) => [$techIds[$slug] => ['sort_order' => $i]]));

            $serviceIds = Service::query()->whereIn('slug', $data['services'])->pluck('id', 'slug');
            $project->services()->sync(collect($data['services'])
                ->filter(fn (string $slug) => $serviceIds->has($slug))
                ->mapWithKeys(fn (string $slug, int $i) => [$serviceIds[$slug] => ['sort_order' => $i]]));
        }
    }

    /** Public cover mockup; re-seeding keeps an already attached copy of the same file. */
    private function attachCover(Project $project, ?string $cover): void
    {
        $path = $cover && config('portfolio.seed_cover_mockups') ? __DIR__.'/media/'.$cover : null;

        if ($path === null || ! is_file($path)
            || $project->getMedia('cover')->contains(fn ($media) => $media->getCustomProperty('source') === $cover)) {
            return;
        }

        $project->addMedia($path)
            ->preservingOriginal()
            ->withCustomProperties(['source' => $cover])
            ->toMediaCollection('cover');
    }
}
