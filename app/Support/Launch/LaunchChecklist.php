<?php

namespace App\Support\Launch;

use App\Models\Experience;
use App\Models\Project;
use App\Models\Testimonial;
use App\Settings\ContactSettings;
use App\Settings\IdentitySettings;
use App\Settings\SeoSettings;
use App\Support\PlaceholderDetector;
use App\Support\Profile;

/**
 * Everything still standing between the site and a confident launch,
 * shown on the admin dashboard.
 */
final class LaunchChecklist
{
    /** Settings fields that must hold real values before launch: [class, property, label, page]. */
    public const array REQUIRED_SETTINGS = [
        [ContactSettings::class, 'contact_email', 'Contact email', 'contact'],
        [ContactSettings::class, 'upwork_url', 'Upwork profile URL', 'contact'],
        [ContactSettings::class, 'linkedin_url', 'LinkedIn profile URL', 'contact'],
        [ContactSettings::class, 'github_url', 'GitHub profile URL', 'contact'],
        [ContactSettings::class, 'whatsapp', 'WhatsApp number', 'contact'],
        [IdentitySettings::class, 'person_name', 'Name', 'identity'],
        [IdentitySettings::class, 'job_title', 'Job title', 'identity'],
        [IdentitySettings::class, 'location', 'Location', 'identity'],
        [IdentitySettings::class, 'availability_note', 'Availability note', 'identity'],
        [IdentitySettings::class, 'hero_headline', 'Hero headline', 'identity'],
        [IdentitySettings::class, 'hero_subheadline', 'Hero subheadline', 'identity'],
        [IdentitySettings::class, 'hero_cta_text', 'Hero CTA text', 'identity'],
    ];

    /**
     * Settings that still hold a placeholder.
     *
     * @return list<array{label: string, page: string, value: string}>
     */
    public function placeholders(): array
    {
        $missing = [];

        foreach (self::REQUIRED_SETTINGS as [$class, $property, $label, $page]) {
            $value = app($class)->{$property};

            if (PlaceholderDetector::isPlaceholder($value)) {
                $missing[] = [
                    'label' => $label,
                    'page' => $page,
                    'value' => is_array($value) ? (string) json_encode($value, JSON_UNESCAPED_UNICODE) : (string) ($value ?? '—'),
                ];
            }
        }

        return $missing;
    }

    /**
     * Advisory items: not blockers, but worth doing before or soon after launch.
     *
     * @return list<array{label: string, ok: bool, hint: string}>
     */
    public function advisories(): array
    {
        $profile = app(Profile::class);

        return [
            [
                'label' => 'Inquiries have a recipient',
                'ok' => $profile->mailRecipient() !== null,
                'hint' => $profile->email()
                    ? 'Using the contact email from Site Settings.'
                    : ($profile->mailRecipient() ? 'Using MAIL_CONTACT_ADDRESS from .env (Site Settings email is a placeholder).' : 'Set a contact email in Site Settings or MAIL_CONTACT_ADDRESS in .env — inquiries are stored but not emailed.'),
            ],
            [
                'label' => 'Search engines may index the site',
                'ok' => app(SeoSettings::class)->indexing_enabled,
                'hint' => 'SEO settings → "Allow indexing". Keep it off on staging.',
            ],
            [
                'label' => 'Years of experience stated',
                'ok' => $profile->yearsExperience() !== null,
                'hint' => 'Identity settings → years of experience. Copy omits the number until set.',
            ],
            [
                'label' => 'At least one testimonial published',
                'ok' => Testimonial::query()->visible()->exists(),
                'hint' => 'Testimonials are hidden until a real (non-placeholder) one is published.',
            ],
            [
                'label' => 'Experience timeline published',
                'ok' => Experience::query()->where('is_published', true)->exists(),
                'hint' => 'Add dates and publish entries under Experience.',
            ],
            [
                'label' => 'Every published project has results',
                'ok' => ! Project::query()->published()->get(['id', 'results'])
                    ->contains(fn (Project $p): bool => blank(strip_tags((string) $p->getTranslation('results', 'en', false)))),
                'hint' => 'Projects → Results. Only verified outcomes.',
            ],
        ];
    }
}
