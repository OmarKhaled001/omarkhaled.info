<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Technology;
use Illuminate\Database\Seeder;

class TaxonomySeeder extends Seeder
{
    public function run(): void
    {
        foreach (require __DIR__.'/data/categories.php' as $order => $name) {
            Category::query()->updateOrCreate(['slug' => $order], ['name' => $name]);
        }

        $sort = 0;
        foreach (Category::query()->get() as $category) {
            $category->update(['sort_order' => $sort++]);
        }

        foreach (require __DIR__.'/data/technologies.php' as $i => [$slug, $name, $domain, $inStack, $url]) {
            Technology::query()->updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'domain' => $domain,
                'show_in_stack' => $inStack,
                'show_on_about' => true,
                'url' => $url,
                'sort_order' => $i,
            ]);
        }
    }
}
