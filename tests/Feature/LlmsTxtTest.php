<?php

use App\Models\Project;
use App\Support\Anonymity\AnonymityGuard;
use Database\Seeders\ContentSeeder;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\TaxonomySeeder;

beforeEach(function () {
    $this->seed([TaxonomySeeder::class, ServiceSeeder::class, ProjectSeeder::class, ContentSeeder::class]);
});

it('serves llms.txt in the llmstxt.org shape', function () {
    $txt = $this->get('/llms.txt')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=utf-8')->getContent();

    expect($txt)->toStartWith("# Omar Khaled\n\n> Omar Khaled is a full-stack Laravel & Filament developer based in Egypt")
        ->toContain('## Services')
        ->toContain('- [Laravel development]('.url('/en/services/laravel-development').')')
        ->toContain('## Selected work')
        ->toContain('Verified facts: 294 automated tests')
        ->toContain('- [LinkedIn](https://www.linkedin.com/in/omar-khaled-890b14396)')
        ->toContain('## Optional');
});

it('serves the full text with services, case studies and FAQs', function () {
    $txt = $this->get('/llms-full.txt')->assertOk()->getContent();

    expect($txt)->toContain('### SaaS product development')
        ->toContain('#### Architecture')
        ->toContain('Q: Do you sign NDAs?')
        ->not->toContain('<p>')
        ->not->toContain('qr-code-saas');
});

it('never exposes placeholders or client identities to AI crawlers', function () {
    $guard = app(AnonymityGuard::class);

    foreach (['/llms.txt', '/llms-full.txt'] as $path) {
        $txt = $this->get($path)->getContent();

        expect($txt)->not->toContain('example.com')->not->toContain('placeholder')->not->toContain('+10000000000');
        Project::query()->get()->each(fn (Project $p) => expect($guard->leaks($p, $txt))->toBe([], "{$path} leaks {$p->slug}"));
    }
});

it('links llms.txt from the footer', function () {
    $this->get('/en')->assertSee('href="'.url('/llms.txt').'"', false);
});
