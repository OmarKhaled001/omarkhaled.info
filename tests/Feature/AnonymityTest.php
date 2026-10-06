<?php

use App\Models\Project;
use App\Presenters\PublicProject;
use App\Support\Anonymity\AnonymityGuard;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('media_private');
    Storage::fake('public');
});

it('exposes only anonymized identity by default', function () {
    $project = Project::factory()->create([
        'client_name' => ['en' => 'Nile Root Exports', 'ar' => 'نايل روت'],
        'live_url' => 'https://nileroot.example',
        'repo_url' => 'https://github.com/someone/nileroot',
    ]);
    $p = new PublicProject($project);

    expect($p->title('en'))->toBe('Platform for a trading company')
        ->and($p->summary('ar'))->toBe('منصة طلبات لشركة تجارية.')
        ->and($p->clientName())->toBeNull()
        ->and($p->liveUrl())->toBeNull()
        ->and($p->repoUrl())->toBeNull()
        ->and($p->logo())->toBeNull()
        ->and($p->screenshots())->toBeEmpty();
});

it('reveals exactly what the toggles allow', function () {
    $project = Project::factory()->create([
        'client_name' => ['en' => 'Nile Root Exports', 'ar' => 'نايل روت'],
        'live_url' => 'https://www.nileroot.example',
        'show_client_name' => true,
        'show_live_link' => true,
    ]);
    $p = new PublicProject($project);

    expect($p->clientName('ar'))->toBe('نايل روت')
        ->and($p->title('en'))->toStartWith('Acme')
        ->and($p->liveUrl())->toBe('https://www.nileroot.example')
        ->and($p->liveHost())->toBe('nileroot.example')
        ->and($p->repoUrl())->toBeNull();
});

it('detects client names, aliases and domains in public fields', function () {
    $project = Project::factory()->create([
        'client_name' => ['en' => 'Nile Root Exports', 'ar' => 'نايل روت'],
        'client_aliases' => ['NileRoot'],
        'live_url' => 'https://nileroot-trading.example',
        'challenge' => ['en' => '<p>Before NILEROOT had a platform…</p>', 'ar' => '<p>كانت نايل روت تعتمد على البريد.</p>'],
    ]);

    $leaks = app(AnonymityGuard::class)->leaksInFields($project->fresh());

    expect($leaks)->toContain('NileRoot', 'نايل روت');
});

it('treats Arabic letter variants as the same word', function () {
    expect(AnonymityGuard::normalize('إسكندرية'))->toBe(AnonymityGuard::normalize('اسكندريه'));
});

it('passes the guard when long-form fields are client-neutral', function () {
    $project = Project::factory()->create(['client_aliases' => ['acme']]);
    $project->update(['client_aliases' => ['zzz-unused']]);

    expect(app(AnonymityGuard::class)->leaksInFields($project->fresh()))->toBe([]);
});

it('stops flagging the name once the client is revealed', function () {
    $project = Project::factory()->create([
        'client_name' => ['en' => 'Nile Root Exports'],
        'challenge' => ['en' => '<p>Nile Root Exports needed one system.</p>'],
        'show_client_name' => true,
        'show_live_link' => true,
    ]);

    expect(app(AnonymityGuard::class)->leaksInFields($project->fresh()))->toBe([]);
});

it('moves screenshots to the public disk only while they are revealed', function () {
    $project = Project::factory()->create();
    $project->addMedia(UploadedFile::fake()->image('shot.png', 800, 500))->toMediaCollection('screenshots');

    expect($project->fresh()->getFirstMedia('screenshots')->disk)->toBe('media_private')
        ->and((new PublicProject($project->fresh()))->screenshots())->toBeEmpty();

    $project->update(['show_screenshots' => true]);
    $media = $project->fresh()->getFirstMedia('screenshots');

    expect($media->disk)->toBe('public')
        ->and(Storage::disk('public')->exists($media->uuid.'/'.$media->file_name))->toBeTrue()
        ->and(Storage::disk('media_private')->allFiles())->toBe([])
        ->and((new PublicProject($project->fresh()))->screenshots())->toHaveCount(1);

    $project->update(['show_screenshots' => false]);

    expect($project->fresh()->getFirstMedia('screenshots')->disk)->toBe('media_private')
        ->and(Storage::disk('public')->allFiles())->toBe([]);
});

it('syncs files uploaded after the project was already revealed', function () {
    $project = Project::factory()->revealed()->create();
    $project->addMedia(UploadedFile::fake()->image('late.png', 600, 400))->toMediaCollection('screenshots');

    expect($project->fresh()->getFirstMedia('screenshots')->disk)->toBe('public');
});

it('sanitizes rich text before it reaches the page', function () {
    $project = Project::factory()->create([
        'challenge' => ['en' => '<p onclick="x()">Hi<script>alert(1)</script> <a href="javascript:evil()">x</a></p>'],
    ]);

    $html = (new PublicProject($project))->section('challenge', 'en');

    expect($html)->not->toContain('<script')->not->toContain('onclick')->not->toContain('javascript:');
});
