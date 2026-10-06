<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Contact channels. Seeded with placeholders; public code must read them through
 * App\Support\Profile, which drops anything still holding a placeholder.
 */
class ContactSettings extends Settings
{
    public string $contact_email;

    public ?string $upwork_url;

    public ?string $linkedin_url;

    public ?string $github_url;

    public ?string $whatsapp;

    public ?string $behance_url;

    public ?string $calendly_url;

    public static function group(): string
    {
        return 'contact';
    }
}
