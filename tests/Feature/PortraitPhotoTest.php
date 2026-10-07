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

function portraitJpeg(int $size = 1400): string
{
    $image = imagecreatetruecolor($size, $size);
    imagefill($image, 0, 0, imagecolorallocate($image, 250, 250, 250));
    imagefilledellipse($image, intdiv($size, 2), intdiv($size, 3), intdiv($size, 3), intdiv($size, 2), imagecolorallocate($image, 150, 110, 90));
    ob_start();
    imagejpeg($image);

    return (string) ob_get_clean();
}

function usePortraitPhoto(): void
{
    Storage::disk('media_private')->put('portrait/photo.jpg', portraitJpeg());
    $identity = app(IdentitySettings::class);
    $identity->portrait = 'portrait/photo.jpg';
    $identity->save();
    Portrait::regenerate();
}

it('hides the portrait section until a photo is uploaded', function () {
    $this->get('/en/about')->assertOk()->assertDontSee('id="hello-title"', false);
});

it('publishes resized WebP/AVIF copies, never the upload, and shows them responsively', function () {
    usePortraitPhoto();

    $files = Storage::disk('public')->files('portrait');
    expect($files)->toContain(collect($files)->first(fn ($f) => str_ends_with($f, '-480.webp')))
        ->and(collect($files)->filter(fn ($f) => str_ends_with($f, '.webp'))->count())->toBe(3)
        ->and(collect($files)->contains(fn ($f) => str_contains($f, 'photo.jpg')))->toBeFalse();

    $this->get('/en/about')->assertOk()
        ->assertSee('id="hello-title"', false)
        ->assertSee('Hello!')
        ->assertSee('<source type="image/webp"', false)
        ->assertSee('-1200.webp', false)
        ->assertSee('alt="Portrait of Omar Khaled."', false)
        ->assertSee('loading="lazy"', false)
        ->assertDontSee('photo.jpg');
    $this->get('/ar/about')->assertOk()->assertSee('أهلًا!');
});

it('uses the photo as the Person image in JSON-LD', function () {
    usePortraitPhoto();

    $this->get('/en')->assertOk()->assertSee('"image":"'.Portrait::image()['src'].'"', false);
});

it('removes the published copies when the photo is removed', function () {
    usePortraitPhoto();

    $identity = app(IdentitySettings::class);
    $identity->portrait = null;
    $identity->save();
    Portrait::regenerate();

    expect(app(IdentitySettings::class)->portrait_images)->toBeNull()
        ->and(Storage::disk('public')->files('portrait'))->toBe([])
        ->and(Storage::disk('media_private')->files('portrait'))->toBe([]);
    $this->get('/en/about')->assertDontSee('id="hello-title"', false);
});
