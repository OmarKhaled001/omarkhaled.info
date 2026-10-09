<?php

namespace App\Models\Concerns;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * AVIF + WebP conversions with responsive srcsets, rendered by <x-picture>.
 * AVIF is skipped when the host's GD build cannot encode it. Conversions are nonOptimized():
 * the encoders already compress at the set quality, and the external optimizers (jpegoptim,
 * cwebp…) would need proc_open, which shared hosts such as Hostinger disable.
 */
trait HasOptimizedImages
{
    public function registerMediaConversions(?Media $media = null): void
    {
        if (self::supportsAvif()) {
            $this->addMediaConversion('avif')
                ->withResponsiveImages()
                ->queued()
                ->nonOptimized()
                ->format('avif')
                ->fit(Fit::Max, 2400, 2400)
                ->quality(55);
        }

        $this->addMediaConversion('webp')
            ->withResponsiveImages()
            ->queued()
            ->nonOptimized()
            ->format('webp')
            ->fit(Fit::Max, 2400, 2400)
            ->quality(78);

        // Small preview for the admin panel and social cards.
        $this->addMediaConversion('thumb')
            ->nonQueued()
            ->nonOptimized()
            ->format('webp')
            ->fit(Fit::Max, 640, 640)
            ->quality(75);
    }

    public static function supportsAvif(): bool
    {
        return function_exists('imageavif') && (gd_info()['AVIF Support'] ?? false);
    }
}
