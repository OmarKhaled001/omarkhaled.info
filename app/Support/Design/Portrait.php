<?php

namespace App\Support\Design;

use App\Settings\IdentitySettings;
use GdImage;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * The About page's word portrait. The uploaded photo stays on the private disk; only a small,
 * contrast-stretched grayscale "light map" is published, and the browser turns it into words
 * (resources/js/word-portrait.js).
 */
final class Portrait
{
    public const string SOURCE_DISK = 'media_private';

    public const string MAP_DISK = 'public';

    public const string DIRECTORY = 'portrait';

    /** Map width in pixels: plenty for word placement, tiny to download (~10 KB). */
    private const int MAP_WIDTH = 180;

    private const int ANALYSIS_WIDTH = 360;

    /** @return array{url: string, width: int, height: int}|null */
    public static function map(): ?array
    {
        try {
            $path = app(IdentitySettings::class)->portrait_map;
        } catch (Throwable) {
            return null;
        }

        $disk = Storage::disk(self::MAP_DISK);
        if (! $path || ! $disk->exists($path) || ! ($size = @getimagesize($disk->path($path)))) {
            return null;
        }

        return ['url' => asset('storage/'.$path), 'width' => $size[0], 'height' => $size[1]];
    }

    /** Rebuilds the light map from the uploaded photo and stores its path (null when no photo). */
    public static function regenerate(): void
    {
        $settings = app(IdentitySettings::class);
        $source = $settings->portrait;
        $map = null;

        if ($source && Storage::disk(self::SOURCE_DISK)->exists($source)
            && ($image = @imagecreatefromstring((string) Storage::disk(self::SOURCE_DISK)->get($source)))) {
            $png = self::lightMap($image);
            $map = self::DIRECTORY.'/map-'.substr(sha1($png), 0, 12).'.png';
            Storage::disk(self::MAP_DISK)->put($map, $png);
        }

        if ($settings->portrait_map !== $map) {
            $settings->portrait_map = $map;
            $settings->save();
        }

        self::prune($source, $map);
    }

    /** Deletes photos and maps that the settings no longer reference. */
    private static function prune(?string $source, ?string $map): void
    {
        foreach ([[self::SOURCE_DISK, $source], [self::MAP_DISK, $map]] as [$disk, $keep]) {
            foreach (Storage::disk($disk)->files(self::DIRECTORY) as $file) {
                if ($file !== $keep) {
                    Storage::disk($disk)->delete($file);
                }
            }
        }
    }

    /**
     * Grayscale, levels stretched to the 2nd–99.5th percentile, mid-tones darkened, then cropped to
     * the lit area (the face) at a 3:4 ratio so the words draw a face rather than a dark frame.
     */
    public static function lightMap(GdImage $image): string
    {
        // Analyse at twice the output size so the crop keeps enough detail.
        $height = max(1, (int) round(imagesy($image) * self::ANALYSIS_WIDTH / imagesx($image)));
        $small = imagescale($image, self::ANALYSIS_WIDTH, $height, IMG_BICUBIC) ?: $image;
        $w = imagesx($small);
        $h = imagesy($small);

        $lum = [];
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $rgb = imagecolorat($small, $x, $y);
                $lum[] = 0.2126 * (($rgb >> 16) & 0xFF) + 0.7152 * (($rgb >> 8) & 0xFF) + 0.0722 * ($rgb & 0xFF);
            }
        }

        $sorted = $lum;
        sort($sorted);
        $low = $sorted[(int) floor(count($sorted) * 0.02)];
        $high = max($low + 1, $sorted[(int) floor((count($sorted) - 1) * 0.995)]);

        $levels = imagecreatetruecolor($w, $h);
        [$minX, $minY, $maxX, $maxY] = [$w, $h, 0, 0];
        foreach ($lum as $i => $value) {
            $v = min(1, max(0, ($value - $low) / ($high - $low))) ** 1.35;
            [$x, $y] = [$i % $w, intdiv($i, $w)];
            $g = (int) round($v * 255);
            imagesetpixel($levels, $x, $y, ($g << 16) | ($g << 8) | $g);
            if ($v > 0.3) {
                [$minX, $minY, $maxX, $maxY] = [min($minX, $x), min($minY, $y), max($maxX, $x), max($maxY, $y)];
            }
        }

        $crop = $maxX > $minX && $maxY > $minY ? self::frame($minX, $minY, $maxX, $maxY, $w, $h) : ['x' => 0, 'y' => 0, 'width' => $w, 'height' => $h];
        $cropped = imagecrop($levels, $crop) ?: $levels;
        $outW = min(self::MAP_WIDTH, imagesx($cropped));
        $outH = max(1, (int) round(imagesy($cropped) * $outW / imagesx($cropped)));
        $final = imagescale($cropped, $outW, $outH, IMG_BICUBIC) ?: $cropped;
        imagefilter($final, IMG_FILTER_GRAYSCALE);
        imagetruecolortopalette($final, false, 256);

        ob_start();
        imagepng($final, null, 9);

        return (string) ob_get_clean();
    }

    /**
     * The lit bounding box, padded and grown to 3:4 around its centre, clamped to the image.
     *
     * @return array{x: int, y: int, width: int, height: int}
     */
    private static function frame(int $minX, int $minY, int $maxX, int $maxY, int $w, int $h): array
    {
        $bw = ($maxX - $minX) * 1.12;
        $bh = ($maxY - $minY) * 1.06;
        if ($bw / $bh > 0.75) {
            $bh = $bw / 0.75;
        } else {
            $bw = $bh * 0.75;
        }
        $bw = min($bw, $w);
        $bh = min($bh, $h);
        $cx = ($minX + $maxX) / 2;
        $cy = ($minY + $maxY) / 2;
        $x = (int) round(min(max(0, $cx - $bw / 2), $w - $bw));
        $y = (int) round(min(max(0, $cy - $bh / 2), $h - $bh));

        return ['x' => $x, 'y' => $y, 'width' => (int) round($bw), 'height' => (int) round($bh)];
    }
}
