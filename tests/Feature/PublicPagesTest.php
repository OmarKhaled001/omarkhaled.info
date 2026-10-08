<?php

use App\Models\Project;
use App\Models\Service;
use App\Support\Anonymity\AnonymityGuard;
use Database\Seeders\ContentSeeder;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\TaxonomySeeder;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->seed([TaxonomySeeder::class, ServiceSeeder::class, ProjectSeeder::class, ContentSeeder::class]);
});

/** @return list<string> */
function publicPaths(): array
{
    $paths = [];
    foreach (['en', 'ar'] as $l) {
        array_push($paths, "/{$l}", "/{$l}/about", "/{$l}/services", "/{$l}/projects", "/{$l}/privacy", "/{$l}/contact");
        foreach (Service::query()->published()->pluck('slug') as $slug) {
            $paths[] = "/{$l}/services/{$slug}";
        }
        foreach (Project::query()->published()->pluck('slug') as $slug) {
            $paths[] = "/{$l}/projects/{$slug}";
        }
    }

    return $paths;
}

it('serves every public page with one h1, a title and a description', function () {
    foreach (publicPaths() as $path) {
        $html = $this->get($path)->assertOk()->getContent();

        expect(substr_count($html, '<h1'))->toBe(1, "{$path} must have exactly one h1")
            ->and($html)->toMatch('/<title>[^<]{10,}<\/title>/')
            ->and($html)->toMatch('/<meta name="description" content="[^"]{40,}">/');
    }
});

it('never leaks a client identifier on any rendered public page (leak check)', function () {
    $guard = app(AnonymityGuard::class);
    $projects = Project::query()->get();

    foreach (publicPaths() as $path) {
        $html = $this->get($path)->getContent();

        foreach ($projects as $project) {
            expect($guard->leaks($project, $html))->toBe([], "{$path} leaks {$project->slug}");
        }
    }
});

it('shows client details, live links and the new sections for revealed projects', function () {
    $this->get('/en/projects/travel-booking-platform')
        ->assertSee('CairoKey — a platform for exploring stays and transport in Egypt')
        ->assertSee('href="https://cairokey.net"', false)
        ->assertSee('Who it’s for')
        ->assertSee('How it works')
        ->assertDontSee('github.com/OmarKhaled001/cairokey'); // private repository

    $this->get('/ar/projects/travel-booking-platform')->assertSee('كايرو كي')->assertSee('لمن صُمّم')->assertSee('كيف يعمل');

    $this->get('/en/projects/print-on-demand-platform')->assertOk()
        ->assertSee('Printalia — connecting design to on-demand production')
        ->assertSee('href="https://github.com/OmarKhaled001/printalia"', false);
});

it('keeps projects without approval anonymized', function () {
    $this->get('/en/projects/dental-scan-saas')->assertOk()->assertDontSee('ArchPrep');
    $this->get('/en/projects')->assertDontSee('ArchPrep');
});

it('hides draft projects', function () {
    $this->get('/en/projects/qr-code-saas')->assertNotFound();
    $this->get('/en/projects')->assertDontSee('QR code SaaS');
});

it('filters projects by type and technology and keeps filtered views out of the index', function () {
    $this->get('/en/projects?type=saas')
        ->assertOk()
        ->assertSee('Dental scan preparation SaaS')
        ->assertDontSee('Ludic — a digital catalogue')
        ->assertSee('noindex, follow', false)
        ->assertSee('<link rel="canonical" href="'.url('/en/projects').'">', false);

    $this->get('/en/projects?tech=nextjs')->assertSee('Secure client-operations panel')->assertDontSee('B2B export platform for');
    $this->get('/en/projects')->assertSee('index, follow', false);
});

it('links services and projects to each other', function () {
    $this->get('/en/services/saas-development')->assertSee(url('/en/projects/dental-scan-saas'), false);
    $this->get('/en/projects/dental-scan-saas')->assertSee(url('/en/services/saas-development'), false);
});

it('keeps the language switcher on the same page', function () {
    $this->get('/en/services/api-integrations')->assertSee('href="'.url('/ar/services/api-integrations').'"', false);
    $this->get('/ar/projects?type=saas')->assertSee('href="'.url('/en/projects').'?type=saas"', false);
});

it('hides placeholder testimonials, experience and contact values', function () {
    $this->get('/en')->assertDontSee('[placeholder]')->assertDontSee('example.com');
    $this->get('/en/about')->assertDontSee('Schemacode');
});

it('renders pages without N+1 queries', function (string $path, int $max) {
    $this->get($path); // warm settings/route caches
    DB::enableQueryLog();
    $this->get($path)->assertOk();

    expect(count(DB::getQueryLog()))->toBeLessThanOrEqual($max);
})->with([
    ['/en', 16],
    ['/en/projects', 16],
    ['/en/projects/b2b-export-platform', 18],
    ['/en/services/filament-admin-panels', 16],
    ['/en/about', 12],
]);
