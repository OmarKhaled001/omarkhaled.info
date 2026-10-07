<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class DesignSettings extends Settings
{
    /** Base accent; every other accent token is derived from it (App\Support\Design\AccentPalette). */
    public string $accent_color;

    /** Uploaded logo for light backgrounds (path on the "public" disk); null = built-in mark. */
    public ?string $logo_light;

    /** Optional logo for dark mode; without it the light logo is recoloured for dark backgrounds. */
    public ?string $logo_dark;

    public static function group(): string
    {
        return 'design';
    }
}
