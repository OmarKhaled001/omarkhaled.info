<?php

use App\Settings\ContactSettings;
use App\Settings\DesignSettings;
use App\Settings\IdentitySettings;
use App\Support\Launch\LaunchChecklist;
use App\Support\Profile;

it('hides placeholder contact values from public consumers', function () {
    $profile = app(Profile::class);

    expect($profile->email())->toBeNull()
        ->and($profile->whatsappUrl())->toBeNull()
        ->and($profile->sameAs())->toBe([
            'https://www.linkedin.com/in/omar-khaled-890b14396',
            'https://github.com/OmarKhaled001',
        ])
        ->and(collect($profile->socialLinks())->pluck('label')->all())->not->toContain('Upwork');
});

it('falls back to MAIL_CONTACT_ADDRESS while the settings email is a placeholder', function () {
    config(['portfolio.contact_address' => 'inbox@omarkhaled.info']);

    expect(app(Profile::class)->mailRecipient())->toBe('inbox@omarkhaled.info');

    $contact = app(ContactSettings::class);
    $contact->contact_email = 'hello@omarkhaled.info';
    $contact->save();
    app()->forgetScopedInstances();

    expect(app(Profile::class)->mailRecipient())->toBe('hello@omarkhaled.info');
});

it('has no recipient when neither the setting nor the env fallback is real', function () {
    config(['portfolio.contact_address' => null]);

    expect(app(Profile::class)->mailRecipient())->toBeNull();
});

it('lists every placeholder setting on the launch checklist', function () {
    $labels = collect(app(LaunchChecklist::class)->placeholders())->pluck('label');

    expect($labels)->toContain('Contact email', 'Upwork profile URL', 'WhatsApp number')
        ->not->toContain('LinkedIn profile URL', 'GitHub profile URL', 'Name');
});

it('renders the accent colour from Site Settings as CSS tokens', function () {
    $design = app(DesignSettings::class);
    $design->accent_color = '#1D4ED8';
    $design->save();

    $this->get('/en')->assertSee('--accent:#1D4ED8', false);
});

it('renders *marked* headline words in the accent colour and escapes the rest', function () {
    $identity = app(IdentitySettings::class);
    $identity->hero_headline = ['en' => 'Apps <b>that</b> *just work*.', 'ar' => 'منصّات *تعمل بثبات*.'];
    $identity->save();
    app()->forgetScopedInstances();
    $profile = app(Profile::class);

    expect($profile->heroHeadline('en'))->toBe('Apps <b>that</b> just work.')
        ->and((string) $profile->heroHeadlineHtml('en'))->toBe('Apps &lt;b&gt;that&lt;/b&gt; <span class="text-accent-text">just work</span>.')
        ->and((string) $profile->heroHeadlineHtml('ar'))->toBe('منصّات <span class="text-accent-text">تعمل بثبات</span>.');

    $this->get('/en')->assertSee('<span class="text-accent-text">just work</span>', false);
});
