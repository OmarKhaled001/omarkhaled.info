<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Project;
use App\Models\Service;
use App\Models\Technology;
use Illuminate\Database\Seeder;

/** Seeds every project ANONYMIZED: all show_* toggles stay false until the client approves. */
class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        foreach (require __DIR__.'/data/projects.php' as $data) {
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
                'solution' => $data['solution'],
                'architecture' => $data['architecture'],
                'results' => null,
                'engagement_type' => $data['engagement_type'],
                'schema_type' => $data['schema_type'],
                'live_url' => $data['live_url'],
                'repo_url' => null,
                'year' => $data['year'],
                'is_published' => $data['is_published'],
                'is_featured' => $data['is_featured'],
                'sort_order' => $data['sort_order'],
                'show_client_name' => false,
                'show_live_link' => false,
                'show_logo' => false,
                'show_screenshots' => false,
                'show_repo_link' => false,
            ]);

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
}
