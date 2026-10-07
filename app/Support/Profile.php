<?php

namespace App\Support;

use App\Settings\CareerSettings;
use App\Settings\ContactSettings;
use App\Settings\IdentitySettings;
use DateTimeZone;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

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
        private readonly CareerSettings $career,
    ) {}

    /** Whether the site also addresses employers (roles note, CV, "hiring" inquiries). */
    public function openToRoles(): bool
    {
        return $this->career->open_to_roles;
    }

    public function rolesNote(?string $locale = null): ?string
    {
        return $this->openToRoles() ? $this->translated($this->career->roles_note, $locale) : null;
    }

    /** Path of the CV on the public disk for a locale, falling back to the other language. */
    public function cvPath(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $disk = Storage::disk('public');
        $candidates = $locale === 'ar' ? [$this->career->cv_ar, $this->career->cv_en] : [$this->career->cv_en, $this->career->cv_ar];

        foreach ($candidates as $path) {
            if ($path && $disk->exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /** Stable download URL (/{locale}/cv), or null while no CV is uploaded. */
    public function cvUrl(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return $this->cvPath($locale) ? route('cv', ['locale' => $locale]) : null;
    }

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
        $seconds = Carbon::now($this->timezone())->getOffset();
        $hours = intdiv($seconds, 3600);
        $minutes = abs(intdiv($seconds % 3600, 60));

        return 'UTC'.($seconds >= 0 ? '+' : '−').abs($hours).($minutes ? ':'.str_pad((string) $minutes, 2, '0', STR_PAD_LEFT) : '');
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

    /** Plain text: the *accent* markers are stripped (meta, structured data, tests). */
    public function heroHeadline(?string $locale = null): ?string
    {
        $headline = $this->translated($this->identity->hero_headline, $locale);

        return $headline === null ? null : str_replace('*', '', $headline);
    }

    /** Escaped headline with *marked words* wrapped in an accent span, for the hero H1. */
    public function heroHeadlineHtml(?string $locale = null): ?HtmlString
    {
        $headline = $this->translated($this->identity->hero_headline, $locale);

        if ($headline === null) {
            return null;
        }

        $html = preg_replace('/\*([^*]+)\*/u', '<span class="text-accent-text">$1</span>', e($headline));

        return new HtmlString(str_replace('*', '', (string) $html));
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
