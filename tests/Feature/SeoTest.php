<?php

use App\Models\Project;
use App\Settings\SeoSettings;
use App\Support\Anonymity\AnonymityGuard;
use App\Support\PlaceholderDetector;
use Database\Seeders\ContentSeeder;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\TaxonomySeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->seed([TaxonomySeeder::class, ServiceSeeder::class, ProjectSeeder::class, ContentSeeder::class]);
});

/** @return array<int, array<string, mixed>> */
function jsonLd(string $html): array
{
    preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
    $data = json_decode($m[1] ?? 'null', true, flags: JSON_THROW_ON_ERROR);

    return $data['@graph'];
}

/** @return list<mixed> */
function flattenValues(array $data): array
{
    $out = [];
    array_walk_recursive($data, function ($v) use (&$out) {
        $out[] = $v;
    });

    return $out;
}

it('emits a valid JSON-LD graph with the core entities on every page', function (string $path) {
    $graph = jsonLd($this->get($path)->assertOk()->getContent());
    $types = array_column($graph, '@type');

    expect($types)->toContain('Person', 'ProfessionalService', 'WebSite')
        ->and(collect($graph)->firstWhere('@type', 'Person')['sameAs'])->toBe([
            'https://www.linkedin.com/in/omar-khaled-890b14396',
            'https://github.com/OmarKhaled001',
        ]);
})->with(['/en', '/ar', '/en/about', '/en/services', '/en/services/api-integrations', '/en/projects', '/ar/projects/b2b-export-platform', '/en/contact', '/en/privacy']);

it('never puts placeholder values into structured data', function () {
    foreach (['/en', '/ar/about', '/en/contact'] as $path) {
        foreach (flattenValues(jsonLd($this->get($path)->getContent())) as $value) {
            if (is_string($value) && str_contains($value, '@')) {
                expect(PlaceholderDetector::isPlaceholder($value))->toBeFalse("placeholder in {$path}: {$value}");
            }
            expect((string) $value)->not->toContain('example.com')->not->toContain('placeholder');
        }
    }
});

it('adds page-specific nodes: FAQPage, Service, project work and breadcrumbs', function () {
    expect(array_column(jsonLd($this->get('/en')->getContent()), '@type'))->toContain('FAQPage');

    $service = jsonLd($this->get('/en/services/filament-admin-panels')->getContent());
    expect(array_column($service, '@type'))->toContain('Service', 'FAQPage', 'BreadcrumbList');

    $work = collect(jsonLd($this->get('/en/projects/dental-scan-saas')->getContent()))->firstWhere('@type', 'SoftwareApplication');
    expect($work['creator'])->toBe(['@id' => url('/').'/#person'])
        ->and($work)->not->toHaveKey('sourceOrganization');

    $crumbs = collect(jsonLd($this->get('/ar/projects/dental-scan-saas')->getContent()))->firstWhere('@type', 'BreadcrumbList');
    expect($crumbs['itemListElement'])->toHaveCount(3);
});

it('names the client in structured data only when revealed', function () {
    $work = collect(jsonLd($this->get('/en/projects/travel-booking-platform')->getContent()))->firstWhere('@type', 'CreativeWork');
    expect($work['sourceOrganization'])->toBe(['@type' => 'Organization', 'name' => 'CairoKey']);

    $anonymized = collect(jsonLd($this->get('/en/projects/dental-scan-saas')->getContent()))
        ->first(fn (array $node) => in_array($node['@type'] ?? null, ['CreativeWork', 'SoftwareApplication'], true));
    expect($anonymized)->not->toHaveKey('sourceOrganization');
});

it('generates an Open Graph image per page and locale', function () {
    $html = $this->get('/ar/projects/b2b-export-platform')->getContent();
    preg_match('#<meta property="og:image" content="([^"]+)">#', $html, $m);

    expect($m[1] ?? null)->toContain('/storage/og/');
    expect(Storage::disk('public')->allFiles('og'))->not->toBeEmpty();
    expect($html)->toContain('<meta name="twitter:card" content="summary_large_image">');
});

it('serves a sitemap with every published page in both locales and hreflang alternates', function () {
    $xml = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=utf-8')->getContent();
    $doc = simplexml_load_string($xml);

    expect($doc)->not->toBeFalse()
        ->and(count($doc->url))->toBe(38)
        ->and($xml)->toContain('<loc>'.url('/ar/projects/dental-scan-saas').'</loc>')
        ->and($xml)->toContain('hreflang="x-default" href="'.url('/en/services/saas-development').'"')
        ->and($xml)->not->toContain('qr-code-saas')
        ->and($xml)->not->toContain('/admin');
});

it('keeps client identities out of the sitemap', function () {
    $xml = $this->get('/sitemap.xml')->getContent();
    $guard = app(AnonymityGuard::class);

    Project::query()->get()->each(fn (Project $p) => expect($guard->leaks($p, $xml))->toBe([]));
});

it('welcomes AI crawlers and points to the sitemap in robots.txt', function () {
    $robots = $this->get('/robots.txt')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=utf-8')->getContent();

    foreach (['GPTBot', 'ClaudeBot', 'PerplexityBot', 'Google-Extended'] as $bot) {
        expect($robots)->toContain("User-agent: {$bot}\nAllow: /");
    }
    expect($robots)->toContain('Sitemap: '.url('/sitemap.xml'))->not->toContain('admin');
});

it('switches the whole site to noindex when indexing is disabled (staging)', function () {
    $seo = app(SeoSettings::class);
    $seo->indexing_enabled = false;
    $seo->google_site_verification = 'abc123';
    $seo->save();

    $this->get('/en')->assertSee('noindex, follow', false)->assertSee('<meta name="google-site-verification" content="abc123">', false);
    expect($this->get('/robots.txt')->getContent())->toBe("User-agent: *\nDisallow: /\n");
});
