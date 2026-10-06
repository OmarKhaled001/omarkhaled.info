<?php

use App\Models\Project;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('media_private');
    Storage::fake('public');
});

it('stores identifiable project media on the private disk under random names', function () {
    $project = Project::factory()->create();

    $media = $project->addMedia(UploadedFile::fake()->image('petrogina-dashboard.png', 1600, 1000))
        ->toMediaCollection('screenshots');

    expect($media->disk)->toBe('media_private')
        ->and($media->file_name)->not->toContain('petrogina')
        ->and($media->getPath())->not->toContain('petrogina')
        ->and(Storage::disk('media_private')->exists($media->uuid.'/'.$media->file_name))->toBeTrue()
        ->and(Storage::disk('public')->allFiles())->toBe([]);
});

it('stores non-identifiable covers on the public disk with webp conversions', function () {
    $project = Project::factory()->create();

    $media = $project->addMedia(UploadedFile::fake()->image('cover.jpg', 1200, 800))
        ->toMediaCollection('cover');

    expect($media->disk)->toBe('public')
        ->and($media->fresh()->hasGeneratedConversion('webp'))->toBeTrue()
        ->and($media->getUrl('webp'))->toEndWith('.webp');
});
