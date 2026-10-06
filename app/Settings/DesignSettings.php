<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class DesignSettings extends Settings
{
    /** Base accent; every other accent token is derived from it (App\Support\Design\AccentPalette). */
    public string $accent_color;

    public static function group(): string
    {
        return 'design';
    }
}
