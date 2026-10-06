<?php

use App\Models\Project;
use App\Settings\IdentitySettings;
use App\Support\Design\Theme;
use Database\Seeders\ContentSeeder;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\TaxonomySeeder;

beforeEach(function () {
    config(['responsecache.debug.enabled' => true]);
    $this->seed([TaxonomySeeder::class, ServiceSeeder::class, ProjectSeeder::class, ContentSeeder::class]);
});

it('sends security headers on every response', function (string $path) {
    $response = $this->get($path);

    expect($response->headers->get('X-Content-Type-Options'))->toBe('nosniff')
        ->and($response->headers->get('X-Frame-Options'))->toBe('DENY')
        ->and($response->headers->get('Referrer-Policy'))->toBe('strict-origin-when-cross-origin')
        ->and($response->headers->get('Permissions-Policy'))->toContain('camera=()')
        ->and($response->headers->get('Content-Security-Policy'))->toContain("frame-ancestors 'none'")->toContain("object-src 'none'");
})->with(['/en', '/en/contact', '/sitemap.xml', '/en/missing-page', '/admin/login']);

it('uses a strict, hash-based CSP without unsafe-inline on public pages', function () {
    $csp = $this->get('/ar/projects/b2b-export-platform')->headers->get('Content-Security-Policy');

    expect($csp)->not->toContain('unsafe-inline')->not->toContain('unsafe-eval')
        ->toContain(Theme::scriptHash())
        ->toContain(Theme::styleHash());
});

it('keeps the inline theme script and accent styles byte-identical to their CSP hashes', function () {
    $html = $this->get('/en')->getContent();

    expect($html)->toContain('<script>'.Theme::script().'</script>')
        ->toContain('<style>'.Theme::accentCss().'</style>');
});

it('has no inline style attributes on public pages (blocked by the CSP)', function () {
    foreach (['/en', '/en/services', '/en/projects', '/en/about', '/en/services/saas-development'] as $path) {
        expect($this->get($path)->getContent())->not->toMatch('/<(?!svg|rect|path|circle|line|text|g)[a-z]+[^>]*\sstyle="/i');
    }
});

it('adds HSTS only in production over HTTPS', function () {
    expect($this->get('/en')->headers->has('Strict-Transport-Security'))->toBeFalse();

    app()->detectEnvironment(fn () => 'production');
    $response = $this->get('https://localhost/en');

    expect($response->headers->get('Strict-Transport-Security'))->toBe('max-age=63072000; includeSubDomains; preload');
});

it('serves the second request for a public page from the full-page cache', function () {
    $this->get('/en/services')->assertHeader('Cache-Control', 'max-age=300, public, s-maxage=600, stale-while-revalidate=86400');
    $second = $this->get('/en/services');

    expect($second->headers->get('X-Cache-Status'))->toBe('HIT')
        ->and($second->headers->get('Cache-Control'))->toContain('public');
});

it('clears the page cache when content changes', function () {
    $this->get('/en/projects/dental-scan-saas')->assertSee('Dental scan preparation SaaS');

    $project = Project::query()->where('slug', 'dental-scan-saas')->firstOrFail();
    $project->anonymized_title = ['en' => 'Renamed dental platform', 'ar' => 'منصة أسنان'];
    $project->save();

    $this->get('/en/projects/dental-scan-saas')->assertSee('Renamed dental platform');
});

it('clears the page cache when settings change', function () {
    $this->get('/en')->assertSee('Laravel platforms and back-offices');

    $identity = app(IdentitySettings::class);
    $identity->hero_headline = ['en' => 'A brand new headline for testing', 'ar' => 'عنوان جديد'];
    $identity->save();

    $this->get('/en')->assertSee('A brand new headline for testing');
});

it('never caches the contact page, redirects or 404s', function () {
    foreach (['/en/contact', '/en/nope'] as $path) {
        $this->get($path);
        expect($this->get($path)->headers->get('X-Cache-Status'))->not->toBe('HIT', "{$path} must not be cached");
    }

    $this->get('/', ['Accept-Language' => 'ar'])->assertRedirect('/ar');

    $this->get('/', ['Accept-Language' => 'en'])->assertRedirect('/en');
});

it('keeps secrets out of .env.example', function () {
    $lines = file(base_path('.env.example'), FILE_IGNORE_NEW_LINES);

    foreach ($lines as $line) {
        if (preg_match('/^([A-Z0-9_]*(KEY|SECRET|PASSWORD|TOKEN)[A-Z0-9_]*)=(.*)$/', $line, $m)) {
            expect(trim($m[3], '"\' '))->toBeIn(['', 'null'], "{$m[1]} must be empty in .env.example");
        }
    }
});
