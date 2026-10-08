<?php

use App\Models\Project;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\TaxonomySeeder;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // Conversions are queued jobs: faking the queue keeps this test fast.
    Queue::fake();
    Storage::fake('public');
    config(['portfolio.seed_cover_mockups' => true]);
    $this->seed([TaxonomySeeder::class, ServiceSeeder::class, ProjectSeeder::class]);
});

it('attaches the cover mockups to the showcased projects on the public disk', function () {
    $covers = Project::query()->with('media')->get()
        ->mapWithKeys(fn (Project $p) => [$p->slug => $p->getFirstMedia('cover')?->getCustomProperty('source')])
        ->filter()->sortKeys()->all();

    expect($covers)->toBe([
        'apparel-design-marketplace' => 'covers/narrva.webp',
        'b2b-export-platform' => 'covers/petrogina-trading.webp',
        'multilingual-corporate-cms' => 'covers/ludic.webp',
        'print-on-demand-platform' => 'covers/printalia.webp',
        'travel-booking-platform' => 'covers/cairokey.webp',
    ]);

    expect(Project::query()->where('slug', 'print-on-demand-platform')->firstOrFail()->getFirstMedia('cover')->disk)->toBe('public');
});

it('does not duplicate covers when seeding again', function () {
    $this->seed(ProjectSeeder::class);

    expect(Project::query()->where('slug', 'b2b-export-platform')->firstOrFail()->getMedia('cover'))->toHaveCount(1);
});
