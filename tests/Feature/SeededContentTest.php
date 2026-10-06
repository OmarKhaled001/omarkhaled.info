<?php

use App\Models\Faq;
use App\Models\Project;
use App\Models\Service;
use App\Models\Testimonial;
use App\Support\Anonymity\AnonymityGuard;
use Database\Seeders\ContentSeeder;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\TaxonomySeeder;

beforeEach(function () {
    $this->seed([TaxonomySeeder::class, ServiceSeeder::class, ProjectSeeder::class, ContentSeeder::class]);
});

it('seeds six published projects and three drafts, all anonymized', function () {
    expect(Project::query()->published()->count())->toBe(6)
        ->and(Project::query()->where('is_published', false)->pluck('slug')->sort()->values()->all())
        ->toBe(['print-on-demand-platform', 'qr-code-saas', 'volunteer-management-system']);

    Project::query()->get()->each(function (Project $p) {
        expect([$p->show_client_name, $p->show_live_link, $p->show_logo, $p->show_screenshots, $p->show_repo_link])
            ->each->toBeFalse();
    });
});

it('has no client identifiers in any seeded public field', function () {
    $guard = app(AnonymityGuard::class);

    Project::query()->with(['features', 'facts'])->get()->each(function (Project $p) use ($guard) {
        expect($guard->leaksInFields($p))->toBe([], "leak in {$p->slug}");
    });
});

it('never puts a client name in a slug', function () {
    $guard = app(AnonymityGuard::class);

    Project::query()->get()->each(function (Project $p) use ($guard) {
        expect($guard->leaks($p, $p->slug))->toBe([]);
    });
});

it('fills every public project field in both languages', function () {
    Project::query()->published()->with(['features', 'facts'])->get()->each(function (Project $p) {
        foreach (['anonymized_title', 'anonymized_summary', 'title', 'summary', 'industry', 'role', 'challenge', 'solution', 'architecture'] as $field) {
            foreach (['en', 'ar'] as $locale) {
                expect($p->getTranslation($field, $locale, false))->not->toBeEmpty("{$p->slug}.{$field}.{$locale}");
            }
        }
        expect($p->facts)->not->toBeEmpty()->and($p->features)->not->toBeEmpty();
        expect($p->getTranslation('results', 'en', false))->toBeEmpty('results must stay empty until verified');
    });
});

it('seeds complete bilingual service pages', function () {
    expect(Service::query()->published()->count())->toBe(6);

    Service::query()->with(['deliverables', 'processSteps', 'faqs', 'projects'])->get()->each(function (Service $s) {
        foreach (['title', 'card_summary', 'headline', 'intro', 'problem', 'meta_title', 'meta_description'] as $field) {
            expect($s->getTranslation($field, 'ar', false))->not->toBeEmpty("{$s->slug}.{$field}.ar");
        }
        expect($s->deliverables)->not->toBeEmpty()
            ->and($s->processSteps)->not->toBeEmpty()
            ->and($s->faqs)->not->toBeEmpty()
            ->and($s->projects)->not->toBeEmpty();
    });
});

it('never claims multi-tenancy', function () {
    $all = json_encode([Service::query()->get()->toArray(), Project::query()->get()->toArray(), Faq::query()->get()->toArray()], JSON_UNESCAPED_UNICODE);

    expect(mb_strtolower($all))->not->toContain('multi-tenan')->not->toContain('multitenan');
});

it('seeds testimonials as hidden placeholders only', function () {
    expect(Testimonial::query()->count())->toBe(3)
        ->and(Testimonial::query()->visible()->count())->toBe(0);
});

it('is idempotent', function () {
    $this->seed([TaxonomySeeder::class, ServiceSeeder::class, ProjectSeeder::class, ContentSeeder::class]);

    expect(Project::query()->count())->toBe(9)
        ->and(Service::query()->count())->toBe(6)
        ->and(Testimonial::query()->count())->toBe(3);
});
