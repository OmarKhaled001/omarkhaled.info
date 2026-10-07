<?php

namespace App\Support\Design;

use App\Settings\DesignSettings;
use GdImage;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * The uploaded logo (Admin -> Settings -> Design) and the favicons generated from it.
 * Everything lives on the "public" disk; without an upload the built-in mark is used.
 */
final class Brand
{
    public const string DISK = 'public';

    public const string DIRECTORY = 'brand';

    /** Generated icons: file name => [size, opaque white background]. */
    private const array ICONS = [
        'favicon-32.png' => [32, false],
        'favicon-192.png' => [192, false],
        'apple-touch-icon.png' => [180, true],
    ];

    /**
     * @return array{url: string, width: int, height: int}|null
     */
    public static function logo(string $variant = 'light'): ?array
    {
        try {
            $design = app(DesignSettings::class);
            $path = $variant === 'dark' ? $design->logo_dark : $design->logo_light;
        } catch (Throwable) {
            return null; // settings not migrated yet
        }

        return $path ? self::image($path) : null;
    }

    /** @return array{url: string, width: int, height: int}|null */
    private static function image(string $path): ?array
    {
        $disk = Storage::disk(self::DISK);

        if (! $disk->exists($path) || ! ($size = @getimagesize($disk->path($path)))) {
            return null;
        }

        return ['url' => asset('storage/'.$path), 'width' => $size[0], 'height' => $size[1]];
    }

    /** URL of a generated icon, or null to use the static defaults in public/. */
    public static function icon(string $name): ?string
    {
        $path = self::DIRECTORY.'/icons/'.$name;

        return self::logo() !== null && Storage::disk(self::DISK)->exists($path) ? asset('storage/'.$path) : null;
    }

    /** Rebuilds the favicons from the light logo (called after the Design settings are saved). */
    public static function regenerateIcons(): void
    {
        $disk = Storage::disk(self::DISK);
        $disk->deleteDirectory(self::DIRECTORY.'/icons');

        $path = app(DesignSettings::class)->logo_light;
        if (! $path || ! $disk->exists($path) || ! ($source = @imagecreatefromstring((string) $disk->get($path)))) {
            return;
        }

        $mark = self::trim($source);

        foreach (self::ICONS as $name => [$size, $opaque]) {
            $disk->put(self::DIRECTORY.'/icons/'.$name, self::square($mark, $size, $opaque));
        }
    }

    /** Deletes uploaded logo files that the settings no longer reference. */
    public static function prune(): void
    {
        $design = app(DesignSettings::class);
        $keep = array_filter([$design->logo_light, $design->logo_dark]);
        $disk = Storage::disk(self::DISK);

        foreach ($disk->files(self::DIRECTORY) as $file) {
            if (! in_array($file, $keep, true)) {
                $disk->delete($file);
            }
        }
    }

    /** Crops away a plain (white or transparent) margin around the mark. */
    private static function trim(GdImage $image): GdImage
    {
        imagesavealpha($image, true);
        $cropped = imagecropauto($image, IMG_CROP_THRESHOLD, 0.25, 0xFFFFFF)
            ?: imagecropauto($image, IMG_CROP_TRANSPARENT);

        return $cropped ?: $image;
    }

    private static function square(GdImage $mark, int $size, bool $opaque): string
    {
        $canvas = imagecreatetruecolor($size, $size);
        imagesavealpha($canvas, true);
        imagealphablending($canvas, false);
        imagefill($canvas, 0, 0, $opaque ? imagecolorallocate($canvas, 255, 255, 255) : imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        imagealphablending($canvas, true);

        $padding = (int) round($size * ($opaque ? 0.14 : 0.04));
        $box = $size - 2 * $padding;
        $w = imagesx($mark);
        $h = imagesy($mark);
        $scale = min($box / $w, $box / $h);
        $dw = max(1, (int) round($w * $scale));
        $dh = max(1, (int) round($h * $scale));
        imagecopyresampled($canvas, $mark, (int) (($size - $dw) / 2), (int) (($size - $dh) / 2), 0, 0, $dw, $dh, $w, $h);

        ob_start();
        imagepng($canvas, null, 9);

        return (string) ob_get_clean();
    }
}
