<?php

namespace App\Support\Media;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Support\FileNamer\FileNamer;

/**
 * Replaces uploaded file names with random ones so public URLs never reveal
 * a client name (e.g. "petrogina-dashboard.png"). Only the original name is random;
 * conversion names derive from it and must stay deterministic.
 */
class UuidFileNamer extends FileNamer
{
    public function originalFileName(string $fileName): string
    {
        return Str::lower((string) Str::ulid());
    }

    public function conversionFileName(string $fileName, Conversion $conversion): string
    {
        return pathinfo($fileName, PATHINFO_FILENAME).'-'.$conversion->getName();
    }

    public function responsiveFileName(string $fileName): string
    {
        return pathinfo($fileName, PATHINFO_FILENAME);
    }
}
