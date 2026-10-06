<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SeoSettings extends Settings
{
    /** Off on staging: every page becomes noindex and robots.txt disallows all. */
    public bool $indexing_enabled;

    public ?string $google_site_verification;

    public ?string $bing_site_verification;

    public static function group(): string
    {
        return 'seo';
    }
}
