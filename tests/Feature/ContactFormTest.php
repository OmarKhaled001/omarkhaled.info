<?php

use App\Enums\SubmissionStatus;
use App\Livewire\ContactForm;
use App\Mail\InquiryReceivedMail;
use App\Mail\NewInquiryMail;
use App\Models\ContactSubmission;
use App\Settings\ContactSettings;
use App\Settings\SpamSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    Mail::fake();
    config(['portfolio.contact_address' => 'inbox@omarkhaled.test']);
});

function validInquiry(array $overrides = []): array
{
    return array_merge([
        'name' => 'Jane Doe',
        'email' => 'jane@company.test',
        'company' => 'Company GmbH',
        'project_type' => 'saas',
        'budget_range' => '15k-30k',
        'message' => 'We need a Laravel SaaS with a Filament back-office for our logistics team.',
    ], $overrides);
}

function fillForm(array $data, string $locale = 'en')
{
    app()->setLocale($locale);
    $component = Livewire::test(ContactForm::class);
    foreach ($data as $key => $value) {
        $component->set($key, $value);
    }

    return $component;
}

it('renders the contact page in both languages', function () {
    $this->get('/en/contact')->assertOk()->assertSee('Start a project')->assertSeeLivewire(ContactForm::class);
    $this->get('/ar/contact')->assertOk()->assertSee('ابدأ مشروعك');
});

it('validates required fields with localized messages', function () {
    fillForm([], 'en')->call('submit')
        ->assertHasErrors(['name' => 'required', 'email' => 'required', 'project_type' => 'required', 'budget_range' => 'required', 'message' => 'required']);

    fillForm(['email' => 'not-an-email'], 'ar')->call('submit')
        ->assertHasErrors(['email'])
        ->assertSee('يجب أن يكون البريد الإلكتروني عنوان بريد إلكتروني صحيحًا.');
});

it('rejects values outside the allowed project types and budgets', function () {
    fillForm(validInquiry(['project_type' => 'crypto-casino', 'budget_range' => '1M']))->call('submit')
        ->assertHasErrors(['project_type', 'budget_range']);
});

it('stores the inquiry and queues the owner mail with reply-to set to the sender', function () {
    $this->travel(10)->seconds();
    $component = fillForm(validInquiry());
    $this->travel(10)->seconds();
    $component->call('submit')->assertHasNoErrors()->assertSet('sent', true)->assertSee('Thank you');

    $submission = ContactSubmission::query()->sole();
    expect($submission->status)->toBe(SubmissionStatus::New)
        ->and($submission->locale)->toBe('en')
        ->and($submission->ip_hash)->toHaveLength(64);

    Mail::assertQueued(NewInquiryMail::class, fn (NewInquiryMail $mail) => $mail->hasTo('inbox@omarkhaled.test')
        && $mail->hasReplyTo('jane@company.test', 'Jane Doe'));
});

it('sends the auto-reply in the language the visitor used, without echoing their message', function () {
    $component = fillForm(validInquiry(), 'ar');
    $this->travel(10)->seconds();
    $component->call('submit');

    Mail::assertQueued(InquiryReceivedMail::class, function (InquiryReceivedMail $mail) {
        $html = $mail->locale('ar')->render();

        return $mail->hasTo('jane@company.test') && $mail->locale === 'ar'
            && str_contains($html, 'dir="rtl"') && str_contains($html, 'مرحبًا Jane')
            && ! str_contains($html, 'logistics team');
    });
});

it('sends the owner mail to the Site Settings email once it is real', function () {
    $settings = app(ContactSettings::class);
    $settings->contact_email = 'hello@omarkhaled.test';
    $settings->save();

    $component = fillForm(validInquiry());
    $this->travel(10)->seconds();
    $component->call('submit');

    Mail::assertQueued(NewInquiryMail::class, fn ($mail) => $mail->hasTo('hello@omarkhaled.test'));
});

it('stores but does not email inquiries when no recipient is configured', function () {
    config(['portfolio.contact_address' => null]);

    $component = fillForm(validInquiry());
    $this->travel(10)->seconds();
    $component->call('submit')->assertSet('sent', true);

    expect(ContactSubmission::query()->count())->toBe(1);
    Mail::assertNotQueued(NewInquiryMail::class);
});

it('silently quarantines honeypot submissions', function () {
    $component = fillForm(validInquiry(['website' => 'https://spam.example']));
    $this->travel(10)->seconds();
    $component->call('submit')->assertSet('sent', true);

    expect(ContactSubmission::query()->sole())
        ->status->toBe(SubmissionStatus::Spam)
        ->spam_reason->toBe('honeypot');
    Mail::assertNothingQueued();
});

it('quarantines submissions sent faster than a human could type (time-trap)', function () {
    fillForm(validInquiry())->call('submit')->assertSet('sent', true);

    expect(ContactSubmission::query()->sole()->spam_reason)->toBe('time-trap');
    Mail::assertNothingQueued();
});

it('quarantines stale forms older than two hours', function () {
    $component = fillForm(validInquiry());
    $this->travel(3)->hours();
    $component->call('submit');

    expect(ContactSubmission::query()->sole()->spam_reason)->toBe('time-trap');
});

it('rate limits a single IP to three inquiries per ten minutes', function () {
    foreach (range(1, 3) as $i) {
        $component = fillForm(validInquiry(['email' => "person{$i}@company.test"]));
        $this->travel(5)->seconds();
        $component->call('submit')->assertSet('sent', true);
    }

    $component = fillForm(validInquiry(['email' => 'fourth@company.test']));
    $this->travel(5)->seconds();
    $component->call('submit')->assertSet('sent', false)->assertSee('Too many messages');

    expect(ContactSubmission::query()->count())->toBe(3);
});

it('sends at most one auto-reply per address per day', function () {
    foreach (range(1, 2) as $i) {
        $component = fillForm(validInquiry());
        $this->travel(5)->seconds();
        $component->call('submit');
    }

    Mail::assertQueuedCount(3); // two owner notifications, one acknowledgement
    Mail::assertQueued(InquiryReceivedMail::class, 1);
});

it('verifies Cloudflare Turnstile when enabled', function () {
    config(['portfolio.turnstile.site_key' => 'site-key', 'portfolio.turnstile.secret_key' => 'secret-key']);
    $spam = app(SpamSettings::class);
    $spam->turnstile_enabled = true;
    $spam->save();

    Http::fake(['challenges.cloudflare.com/*' => Http::sequence()
        ->push(['success' => false])
        ->push(['success' => true])]);

    $component = fillForm(validInquiry(['turnstileToken' => 'bad']));
    $this->travel(5)->seconds();
    $component->call('submit')->assertSet('sent', false)->assertSee('spam check');

    $component->set('turnstileToken', 'good')->call('submit')->assertSet('sent', true);

    Http::assertSentCount(2);
});

it('prunes spam after 30 days and inquiries after 24 months', function () {
    $oldSpam = ContactSubmission::query()->create(validInquiry() + ['locale' => 'en', 'status' => SubmissionStatus::Spam]);
    $recentSpam = ContactSubmission::query()->create(validInquiry() + ['locale' => 'en', 'status' => SubmissionStatus::Spam]);
    $ancient = ContactSubmission::query()->create(validInquiry() + ['locale' => 'en']);
    $current = ContactSubmission::query()->create(validInquiry() + ['locale' => 'en']);

    ContactSubmission::query()->whereKey($oldSpam->id)->update(['created_at' => now()->subDays(31)]);
    ContactSubmission::query()->whereKey($ancient->id)->update(['created_at' => now()->subMonths(25)]);

    $this->artisan('model:prune', ['--model' => [ContactSubmission::class]]);

    expect(ContactSubmission::query()->pluck('id')->sort()->values()->all())->toBe([$recentSpam->id, $current->id]);
});
