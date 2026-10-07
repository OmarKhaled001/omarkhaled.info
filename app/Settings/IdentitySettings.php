<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/** Who Omar is, as stated on the site. Translatable values are {en, ar} arrays. */
class IdentitySettings extends Settings
{
    /** @var array<string, string> */
    public array $person_name;

    /** @var array<string, string> */
    public array $job_title;

    /** @var array<string, string> */
    public array $location;

    public string $country_code;

    public string $timezone;

    public string $working_hours;

    public string $availability_status;

    /** @var array<string, string> */
    public array $availability_note;

    public int $response_time_hours;

    public ?int $years_experience;

    /** @var array<string, string> */
    public array $hero_headline;

    /** @var array<string, string> */
    public array $hero_subheadline;

    /** @var array<string, string> */
    public array $hero_cta_text;

    /** Original portrait photo on the private "media_private" disk; never served publicly. */
    public ?string $portrait;

    /** Small grayscale light map generated from the portrait (public disk), drawn as words on About. */
    public ?string $portrait_map;

    public static function group(): string
    {
        return 'identity';
    }
}
