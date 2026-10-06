<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Support\Locales;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

/** XML sitemap: every published page in both locales, each with hreflang alternates. */
class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $contentUpdated = collect([
            Project::query()->published()->max('updated_at'),
            Service::query()->published()->max('updated_at'),
            Faq::query()->max('updated_at'),
            Page::query()->max('updated_at'),
        ])->filter()->map(fn ($d) => Carbon::parse($d))->max() ?? now();

        $entries = [
            ['home', [], $contentUpdated, '1.0'],
            ['services.index', [], Service::query()->published()->max('updated_at'), '0.9'],
            ['projects.index', [], Project::query()->published()->max('updated_at'), '0.9'],
            ['about', [], Page::query()->where('key', 'about')->value('updated_at'), '0.7'],
            ['contact', [], $contentUpdated, '0.6'],
            ['privacy', [], Page::query()->where('key', 'privacy')->value('updated_at'), '0.2'],
        ];

        foreach (Service::query()->published()->ordered()->get(['slug', 'updated_at']) as $service) {
            $entries[] = ['services.show', ['slug' => $service->slug], $service->updated_at, '0.8'];
        }

        foreach (Project::query()->published()->ordered()->get(['slug', 'updated_at']) as $project) {
            $entries[] = ['projects.show', ['slug' => $project->slug], $project->updated_at, '0.8'];
        }

        $urls = [];
        foreach ($entries as [$route, $params, $lastmod, $priority]) {
            $alternates = collect(Locales::SUPPORTED)->mapWithKeys(fn (string $l) => [$l => route($route, $params + ['locale' => $l])])->all();

            foreach ($alternates as $loc) {
                $urls[] = [
                    'loc' => $loc,
                    'lastmod' => Carbon::parse($lastmod ?? $contentUpdated)->toAtomString(),
                    'priority' => $priority,
                    'alternates' => $alternates,
                ];
            }
        }

        return response()
            ->view('seo.sitemap', ['urls' => $urls, 'xDefault' => Locales::DEFAULT])
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
}
