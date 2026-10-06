<?php

namespace Database\Seeders;

use App\Support\Media\ScreenshotImporter;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Idempotent: safe to re-run; it never creates users or overwrites Site Settings. */
    public function run(): void
    {
        $this->call([
            TaxonomySeeder::class,
            ServiceSeeder::class,
            ProjectSeeder::class,
            ContentSeeder::class,
        ]);

        // Client screenshots are gitignored; import them only where the capture folder exists.
        $result = app(ScreenshotImporter::class)->import(storage_path('app/seed-media/screenshots'));

        if ($result['imported'] > 0) {
            $this->command?->info("Imported {$result['imported']} private screenshot(s).");
        }
    }
}
