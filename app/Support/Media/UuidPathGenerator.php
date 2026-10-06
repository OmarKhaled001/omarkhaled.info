<?php

namespace App\Support\Media;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

/** Stores media under its UUID instead of the sequential id, so paths are unguessable. */
class UuidPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $media->uuid.'/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $media->uuid.'/c/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $media->uuid.'/r/';
    }
}
