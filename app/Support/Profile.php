<?php

namespace App\Support;

use App\Settings\ContactSettings;
use App\Settings\IdentitySettings;
use DateTimeZone;
use Illuminate\Support\Carbon;

/**
 * The only public gateway to identity and contact data. Every value that still holds a
 * placeholder comes back as null, so pages, JSON-LD, the sitemap, llms.txt, OG images and
 * mail headers can never leak "contact@example.com" and friends.
 */
final class Profile
{
    /** Social profiles that are also Person.sameAs identities. */
    private const array SOCIALS = [
        'linkedin_url' => 'LinkedIn',
        'github_url' => 'GitHub',
        'upwork_url' => 'Upwork',
        'behance_url' => 'Behance',
    ];

    public function __construct(
        private readonly IdentitySettings $identity,
        private readonly ContactSettings $contact,
    ) {}

    public function name(?string $locale = null): string
    {
        return $this->translated($this->identity->person_name, $locale) ?? 'Omar Khaled';
    }

    public function jobTitle(?string $locale = null): string
    {
        return $this->translated($this->identity->job_title, $locale) ?? '';
    }

    public function location(?string $locale = null): string
    {
        return $this->translated($this->identity->location, $locale) ?? '';
    }

    public function countryCode(): string
    {
        return $this->identity->country_code;
    }

    public function timezone(): string
    {
        return in_array($this->identity->timezone, DateTimeZone::listIdentifiers(), true)
            ? $this->identity->timezone
            : 'Africa/Cairo';
    }

    /** e.g. "UTC+3" right now (Egypt observes DST). */
    public function utcOffsetLabel(): string
    {
        $offset = Carbon::now($this->timezone())->format('P');

        return 'UTC'.preg_replace('/:00$/', '', $offset);
    }

    public function workingHours(): string
    {
        return $this->identity->working_hours;
    }

    public function availabilityStatus(): string
    {
        return $this->identity->availability_status;
    }

    public function availabilityNote(?string $locale = null): ?string
    {
        return $this->translated($this->identity->availability_note, $locale);
    }

    public function responseTimeHours(): int
    {
        return max(1, $this->identity->response_time_hours);
    }

    public function yearsExperience(): ?int
    {
        return $this->identity->years_experience ?: null;
    }

    public function heroHeadline(?string $locale = null): ?string
    {
        return $this->translated($this->identity->hero_headline, $locale);
    }

    public function heroSubheadline(?string $locale = null): ?string
    {
        return $this->translated($this->identity->hero_subheadline, $locale);
    }

    public function heroCtaText(?string $locale = null): string
    {
        return $this->translated($this->identity->hero_cta_text, $locale) ?? __('site.cta.start_project');
    }

    public function email(): ?string
    {
        $email = PlaceholderDetector::real($this->contact->contact_email);

        return is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    /** E.164 number without "+", for wa.me links. */
    public function whatsappNumber(): ?string
    {
        $number = PlaceholderDetector::real($this->contact->whatsapp);

        if (! is_string($number)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $number);

        return strlen((string) $digits) >= 8 ? $digits : null;
    }

    public function whatsappUrl(): ?string
    {
        $number = $this->whatsappNumber();

        return $number ? "https://wa.me/{$number}" : null;
    }

    public function calendlyUrl(): ?string
    {
        return $this->url($this->contact->calendly_url);
    }

    /** @return list<array{key: string, label: string, url: string}> */
    public function socialLinks(): array
    {
        $links = [];

        foreach (self::SOCIALS as $field => $label) {
            if ($url = $this->url($this->contact->{$field})) {
                $links[] = ['key' => str_replace('_url', '', $field), 'label' => $label, 'url' => $url];
            }
        }

        return $links;
    }

    /** @return list<string> */
    public function sameAs(): array
    {
        return array_column($this->socialLinks(), 'url');
    }

    /** Inquiry recipient: Site Settings email, else MAIL_CONTACT_ADDRESS, else null. */
    public function mailRecipient(): ?string
    {
        if ($email = $this->email()) {
            return $email;
        }

        $fallback = config('portfolio.contact_address');

        return is_string($fallback) && filter_var($fallback, FILTER_VALIDATE_EMAIL) && ! PlaceholderDetector::isPlaceholder($fallback)
            ? $fallback
            : null;
    }

    private function url(?string $value): ?string
    {
        $value = PlaceholderDetector::real($value);

        return is_string($value) && filter_var($value, FILTER_VALIDATE_URL) && str_starts_with($value, 'https://')
            ? $value
            : null;
    }

    /** @param array<string, string>|null $values */
    private function translated(?array $values, ?string $locale): ?string
    {
        $locale ??= app()->getLocale();
        $value = $values[$locale] ?? $values[Locales::DEFAULT] ?? null;

        return PlaceholderDetector::isPlaceholder($value) ? null : $value;
    }
}
