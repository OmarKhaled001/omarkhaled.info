<?php

namespace App\Support\Design;

use App\Settings\IdentitySettings;
use GdImage;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * The portrait photo on the About page. The upload stays on the private disk; saving the Identity
 * settings publishes resized AVIF + WebP copies (metadata stripped by re-encoding) for a responsive
 * <picture>, and the largest WebP doubles as the Person image in JSON-LD.
 */
final class Portrait
{
    public const string SOURCE_DISK = 'media_private';

    public const string PUBLIC_DISK = 'public';

    public const string DIRECTORY = 'portrait';

    /** @var list<int> */
    private const array WIDTHS = [480, 800, 1200];

    /**
     * @return array{src: string, width: int, height: int, sources: array<string, string>}|null
     */
    public static function image(): ?array
    {
        try {
            $images = self::rows(app(IdentitySettings::class)->portrait_images);
        } catch (Throwable) {
            return null;
        }

        $disk = Storage::disk(self::PUBLIC_DISK);
        $images = array_values(array_filter($images, fn (array $i): bool => $disk->exists($i['path'])));
        $webp = array_values(array_filter($images, fn (array $i): bool => $i['format'] === 'webp'));
        if ($webp === []) {
            return null;
        }

        usort($webp, fn (array $a, array $b): int => $a['width'] <=> $b['width']);
        $largest = $webp[array_key_last($webp)];
        $sources = [];
        foreach (['avif', 'webp'] as $format) {
            $set = array_filter($images, fn (array $i): bool => $i['format'] === $format);
            if ($set !== []) {
                $sources["image/{$format}"] = implode(', ', array_map(fn (array $i): string => asset('storage/'.$i['path']).' '.$i['width'].'w', $set));
            }
        }

        return ['src' => asset('storage/'.$largest['path']), 'width' => $largest['width'], 'height' => $largest['height'], 'sources' => $sources];
    }

    /** Rebuilds the web copies from the uploaded photo (called after the Identity settings are saved). */
    public static function regenerate(): void
    {
        $settings = app(IdentitySettings::class);
        $source = $settings->portrait;
        $images = [];

        if ($source && Storage::disk(self::SOURCE_DISK)->exists($source)
            && ($photo = @imagecreatefromstring((string) Storage::disk(self::SOURCE_DISK)->get($source)))) {
            $images = self::publish($photo, substr(sha1((string) Storage::disk(self::SOURCE_DISK)->get($source)), 0, 12));
        }

        $json = $images === [] ? null : (string) json_encode($images, JSON_UNESCAPED_SLASHES);
        if ($settings->portrait_images !== $json) {
            $settings->portrait_images = $json;
            $settings->save();
        }

        self::prune($source, array_column($images, 'path'));
    }

    /** @return list<array{format: string, width: int, height: int, path: string}> */
    private static function rows(?string $json): array
    {
        $rows = json_decode((string) $json, true);

        return is_array($rows) ? array_values(array_filter($rows, fn ($r): bool => is_array($r)
            && is_string($r['format'] ?? null) && is_string($r['path'] ?? null) && is_int($r['width'] ?? null) && is_int($r['height'] ?? null))) : [];
    }

    /** @return list<array{format: string, width: int, height: int, path: string}> */
    private static function publish(GdImage $photo, string $hash): array
    {
        imagesavealpha($photo, true);
        $images = [];
        $widths = array_values(array_unique(array_map(fn (int $w): int => min($w, imagesx($photo)), self::WIDTHS)));

        foreach ($widths as $width) {
            $height = max(1, (int) round(imagesy($photo) * $width / imagesx($photo)));
            $resized = imagescale($photo, $width, $height, IMG_BICUBIC) ?: $photo;

            foreach (['avif', 'webp'] as $format) {
                if ($format === 'avif' && ! function_exists('imageavif')) {
                    continue;
                }

                ob_start();
                $format === 'avif' ? imageavif($resized, null, 55, 6) : imagewebp($resized, null, 82);
                $path = self::DIRECTORY."/photo-{$hash}-{$width}.{$format}";
                Storage::disk(self::PUBLIC_DISK)->put($path, (string) ob_get_clean());
                $images[] = ['format' => $format, 'width' => $width, 'height' => $height, 'path' => $path];
            }
        }

        return $images;
    }

    /**
     * Deletes uploads and published copies the settings no longer reference.
     *
     * @param  list<string>  $published
     */
    private static function prune(?string $source, array $published): void
    {
        foreach (Storage::disk(self::SOURCE_DISK)->files(self::DIRECTORY) as $file) {
            if ($file !== $source) {
                Storage::disk(self::SOURCE_DISK)->delete($file);
            }
        }

        foreach (Storage::disk(self::PUBLIC_DISK)->files(self::DIRECTORY) as $file) {
            if (! in_array($file, $published, true)) {
                Storage::disk(self::PUBLIC_DISK)->delete($file);
            }
        }
    }
}
