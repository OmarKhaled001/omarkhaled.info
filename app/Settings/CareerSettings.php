<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/** Hiring side of the site: openness to roles and the downloadable CV (files on the public disk). */
class CareerSettings extends Settings
{
    public bool $open_to_roles;

    /** @var array<string, string> */
    public array $roles_note;

    /** Path on the "public" disk, e.g. "cv/9f…​.pdf". */
    public ?string $cv_en;

    /** Optional Arabic CV; Arabic pages fall back to the English one. */
    public ?string $cv_ar;

    public static function group(): string
    {
        return 'career';
    }
}
