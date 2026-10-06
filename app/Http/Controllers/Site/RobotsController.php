<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Settings\SeoSettings;
use Illuminate\Http\Response;
use Throwable;

class RobotsController extends Controller
{
    /** Reputable AI and search crawlers we explicitly welcome (GEO). */
    public const array AI_CRAWLERS = [
        'GPTBot', 'OAI-SearchBot', 'ChatGPT-User',
        'ClaudeBot', 'Claude-SearchBot', 'Claude-User',
        'PerplexityBot', 'Perplexity-User',
        'Google-Extended', 'Applebot-Extended', 'Bingbot', 'Googlebot',
    ];

    public function __invoke(): Response
    {
        try {
            $indexing = app(SeoSettings::class)->indexing_enabled;
        } catch (Throwable) {
            $indexing = true;
        }

        $lines = [];

        if (! $indexing) {
            // Staging: keep everything out of every index.
            $lines = ['User-agent: *', 'Disallow: /'];
        } else {
            foreach (self::AI_CRAWLERS as $bot) {
                array_push($lines, "User-agent: {$bot}", 'Allow: /', '');
            }
            // The admin path is intentionally not listed here; it sends X-Robots-Tag: noindex instead.
            array_push($lines, 'User-agent: *', 'Allow: /', 'Disallow: /livewire/', '', 'Sitemap: '.route('sitemap'));
        }

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }
}
