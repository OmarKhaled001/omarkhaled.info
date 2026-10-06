<?php

namespace App\Console\Commands;

use App\Support\Media\ScreenshotImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('portfolio:import-media {path? : Folder containing manifest.json (default: storage/app/seed-media/screenshots)}')]
#[Description('Import captured project screenshots into private media (hidden until a project reveals them)')]
class ImportMediaCommand extends Command
{
    public function handle(ScreenshotImporter $importer): int
    {
        $path = $this->argument('path') ?: storage_path('app/seed-media/screenshots');
        $result = $importer->import($path);

        $this->components->info("Imported {$result['imported']} screenshot(s), skipped {$result['skipped']}. Conversions run on the queue.");

        return self::SUCCESS;
    }
}
