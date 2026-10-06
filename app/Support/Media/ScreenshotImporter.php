<?php

namespace App\Support\Media;

use App\Models\Project;
use Illuminate\Support\Facades\File;

/**
 * Imports captured screenshots (see storage/app/seed-media/screenshots/manifest.json) into each
 * project's PRIVATE "screenshots" collection. They stay unpublished until show_screenshots is on.
 */
final class ScreenshotImporter
{
    /** Capture folder => project slug. */
    public const array PROJECTS = [
        'petrogina-trading' => 'b2b-export-platform',
        'cairo-key' => 'travel-booking-platform',
        'printalia' => 'print-on-demand-platform',
    ];

    /**
     * @return array{imported: int, skipped: int}
     */
    public function import(string $directory): array
    {
        $manifestPath = rtrim($directory, '/').'/manifest.json';

        if (! File::exists($manifestPath)) {
            return ['imported' => 0, 'skipped' => 0];
        }

        /** @var list<array{project: string, url: string, device: string, file: string}> $manifest */
        $manifest = json_decode(File::get($manifestPath), true, flags: JSON_THROW_ON_ERROR);
        $imported = $skipped = 0;

        foreach ($manifest as $entry) {
            $slug = self::PROJECTS[$entry['project']] ?? null;
            $project = $slug ? Project::query()->where('slug', $slug)->first() : null;
            $path = rtrim($directory, '/').'/'.$entry['file'];

            // Viewport captures only: full-page captures of scroll-pinned sections render poorly.
            if (! $project || ! File::exists($path) || str_ends_with($entry['file'], '-full.png')) {
                $skipped++;

                continue;
            }

            $alreadyImported = $project->getMedia('screenshots')
                ->contains(fn ($media) => $media->getCustomProperty('source') === $entry['file']);

            if ($alreadyImported) {
                $skipped++;

                continue;
            }

            $project->addMedia($path)
                ->preservingOriginal()
                ->withCustomProperties([
                    'source' => $entry['file'],
                    'device' => $entry['device'],
                    'page' => parse_url($entry['url'], PHP_URL_PATH) ?: '/',
                ])
                ->toMediaCollection('screenshots');

            $imported++;
        }

        return ['imported' => $imported, 'skipped' => $skipped];
    }
}
