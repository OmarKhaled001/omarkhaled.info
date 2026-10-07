<?php

use App\Settings\IdentitySettings;
use App\Support\Design\Portrait;
use Database\Seeders\ContentSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(ContentSeeder::class);
    Storage::fake('public');
    Storage::fake('media_private');
});

/** A dark photo with a lit "face" in the upper middle. */
function portraitPhoto(): string
{
    $image = imagecreatetruecolor(600, 800);
    imagefill($image, 0, 0, imagecolorallocate($image, 8, 8, 8));
    imagefilledellipse($image, 300, 300, 260, 340, imagecolorallocate($image, 200, 170, 150));
    ob_start();
    imagejpeg($image);

    return (string) ob_get_clean();
}

function usePortrait(): void
{
    Storage::disk('media_private')->put('portrait/photo.jpg', portraitPhoto());
    $identity = app(IdentitySettings::class);
    $identity->portrait = 'portrait/photo.jpg';
    $identity->save();
    Portrait::regenerate();
}

it('hides the word portrait section until a portrait is uploaded', function () {
    $this->get('/en/about')->assertOk()->assertDontSee('data-word-portrait', false);
});

it('publishes only a small light map cropped to the face, never the photo', function () {
    usePortrait();

    $map = app(IdentitySettings::class)->portrait_map;
    [$width, $height] = getimagesizefromstring((string) Storage::disk('public')->get($map));

    expect($width)->toBeLessThanOrEqual(180)
        ->and($width / $height)->toEqualWithDelta(0.75, 0.03)
        ->and(Storage::disk('public')->files('portrait'))->toBe([$map]);

    $this->get('/en/about')->assertOk()
        ->assertSee('data-word-portrait', false)
        ->assertSee('storage/'.$map, false)
        ->assertDontSee('photo.jpg')
        ->assertSee('Hello!')
        ->assertSee('Portrait of Omar Khaled, drawn with words.');
    $this->get('/ar/about')->assertOk()->assertSee('أهلًا!');
});

it('removes the map and old files when the portrait is removed', function () {
    usePortrait();

    $identity = app(IdentitySettings::class);
    $identity->portrait = null;
    $identity->save();
    Portrait::regenerate();

    expect(app(IdentitySettings::class)->portrait_map)->toBeNull()
        ->and(Storage::disk('public')->files('portrait'))->toBe([])
        ->and(Storage::disk('media_private')->files('portrait'))->toBe([]);
    $this->get('/en/about')->assertDontSee('data-word-portrait', false);
});
