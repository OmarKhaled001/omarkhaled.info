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

    /** Display height of the generated web logo: 3.5× the 32 px header logo, for sharp HiDPI. */
    private const int WEB_LOGO_HEIGHT = 112;

    /**
     * The logo as shown on the site: the small generated WebP when it exists, else the upload.
     *
     * @return array{url: string, width: int, height: int}|null
     */
    public static function logo(string $variant = 'light'): ?array
    {
        $path = self::uploaded($variant);
        if ($path === null) {
            return null;
        }

        $web = self::DIRECTORY.'/icons/logo-'.$variant.'.webp';

        return self::image(Storage::disk(self::DISK)->exists($web) ? $web : $path);
    }

    /** Upload path for a variant; a dark logo identical to the light one counts as no dark logo. */
    private static function uploaded(string $variant): ?string
    {
        try {
            $design = app(DesignSettings::class);
        } catch (Throwable) {
            return null; // settings not migrated yet
        }

        $disk = Storage::disk(self::DISK);
        $light = $design->logo_light && $disk->exists($design->logo_light) ? $design->logo_light : null;

        if ($variant !== 'dark') {
            return $light;
        }

        $dark = $design->logo_dark && $disk->exists($design->logo_dark) ? $design->logo_dark : null;

        return $dark && ! ($light && hash_equals((string) sha1_file($disk->path($light)), (string) sha1_file($disk->path($dark)))) ? $dark : null;
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

    /**
     * Rebuilds the favicons and the small web logos from the uploads (called after the Design
     * settings are saved): uploads are often 1000+ px PNGs of several hundred KB, far too heavy
     * for a 32 px header logo.
     */
    public static function regenerateIcons(): void
    {
        $disk = Storage::disk(self::DISK);
        $disk->deleteDirectory(self::DIRECTORY.'/icons');

        foreach (['light', 'dark'] as $variant) {
            $path = self::uploaded($variant);
            if (! $path || ! ($source = @imagecreatefromstring((string) $disk->get($path)))) {
                continue;
            }

            $mark = self::trim($source);
            $disk->put(self::DIRECTORY.'/icons/logo-'.$variant.'.webp', self::webLogo($mark));

            if ($variant === 'light') {
                foreach (self::ICONS as $name => [$size, $opaque]) {
                    $disk->put(self::DIRECTORY.'/icons/'.$name, self::square($mark, $size, $opaque));
                }
            }
        }
    }

    private static function webLogo(GdImage $mark): string
    {
        $height = min(self::WEB_LOGO_HEIGHT, imagesy($mark));
        $width = max(1, (int) round(imagesx($mark) * $height / imagesy($mark)));
        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        imagecopyresampled($canvas, $mark, 0, 0, 0, 0, $width, $height, imagesx($mark), imagesy($mark));

        ob_start();
        imagewebp($canvas, null, 90);

        return (string) ob_get_clean();
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

    /** Crops away a plain margin around the mark: transparent or near-white pixels count as background. */
    private static function trim(GdImage $image): GdImage
    {
        if (! imageistruecolor($image)) {
            imagepalettetotruecolor($image);
        }
        imagesavealpha($image, true);

        $w = imagesx($image);
        $h = imagesy($image);
        [$minX, $minY, $maxX, $maxY] = [$w, $h, -1, -1];
        $step = max(1, intdiv(max($w, $h), 600)); // sampling is plenty for a bounding box

        for ($y = 0; $y < $h; $y += $step) {
            for ($x = 0; $x < $w; $x += $step) {
                $c = imagecolorat($image, $x, $y);
                $alpha = ($c >> 24) & 0x7F;
                $isBackground = $alpha > 100 || ((($c >> 16) & 0xFF) > 235 && (($c >> 8) & 0xFF) > 235 && ($c & 0xFF) > 235);
                if (! $isBackground) {
                    [$minX, $minY, $maxX, $maxY] = [min($minX, $x), min($minY, $y), max($maxX, $x), max($maxY, $y)];
                }
            }
        }

        if ($maxX < 0) {
            return $image;
        }

        $pad = $step;
        $rect = [
            'x' => max(0, $minX - $pad),
            'y' => max(0, $minY - $pad),
            'width' => min($w, $maxX + $pad + 1) - max(0, $minX - $pad),
            'height' => min($h, $maxY + $pad + 1) - max(0, $minY - $pad),
        ];

        return imagecrop($image, $rect) ?: $image;
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
