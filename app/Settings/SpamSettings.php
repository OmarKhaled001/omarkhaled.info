<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/** Turnstile keys live in .env; this only switches the check on. */
class SpamSettings extends Settings
{
    public bool $turnstile_enabled;

    public static function group(): string
    {
        return 'spam';
    }
}
