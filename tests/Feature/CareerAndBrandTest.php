<?php

use App\Livewire\ContactForm;
use App\Mail\NewInquiryMail;
use App\Models\ContactSubmission;
use App\Settings\CareerSettings;
use App\Settings\DesignSettings;
use App\Support\Design\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
});

function uploadCv(string $locale): void
{
    $path = UploadedFile::fake()->create("cv-{$locale}.pdf", 120, 'application/pdf')->store('cv', 'public');
    $career = app(CareerSettings::class);
    $career->{"cv_{$locale}"} = $path;
    $career->save();
    app()->forgetScopedInstances();
}

function pngLogo(): string
{
    $image = imagecreatetruecolor(400, 300);
    imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));
    imagefilledrectangle($image, 120, 80, 280, 220, imagecolorallocate($image, 20, 20, 20));
    ob_start();
    imagepng($image);

    return (string) ob_get_clean();
}

it('hides every CV button and 404s the CV route until a CV is uploaded', function () {
    $this->get('/en')->assertOk()->assertDontSee('Download CV');
    $this->get('/en/cv')->assertNotFound();
});

it('offers the uploaded CV under a readable file name, with Arabic falling back to English', function () {
    uploadCv('en');

    $this->get('/en')->assertSee('Download CV')->assertSee(route('cv', ['locale' => 'en']), false);
    $this->get('/ar')->assertSee('تحميل السيرة الذاتية');

    $this->get('/en/cv')->assertOk()->assertDownload('omar-khaled-CV-EN.pdf');
    $this->get('/ar/cv')->assertOk()->assertDownload('omar-khaled-CV-AR.pdf');
});

it('addresses both clients and employers while open to roles', function () {
    $this->get('/en')->assertSee('Two ways to work together')->assertSee('Hire me for your team')
        ->assertSee('Open to full-time, contract and remote roles')
        ->assertSee('/en/contact?type=job-role', false);
    $this->get('/ar')->assertSee('وظّفني في فريقك');

    $career = app(CareerSettings::class);
    $career->open_to_roles = false;
    $career->save();

    $this->get('/en')->assertDontSee('Hire me for your team')->assertDontSee('Open to full-time');
});

it('takes role inquiries without a budget and preselects them from the link', function () {
    Mail::fake();
    config(['portfolio.contact_address' => 'inbox@omarkhaled.test']);

    $this->get('/en/contact?type=job-role')->assertOk()->assertSee('A job or contract role');

    $component = Livewire::withQueryParams(['type' => 'job-role'])->test(ContactForm::class)
        ->assertSet('project_type', 'job-role')
        ->assertDontSee('Budget range')
        ->set('name', 'Hiring Manager')
        ->set('email', 'talent@company.test')
        ->set('message', 'We are hiring a senior Laravel developer for our remote platform team.');
    $this->travel(10)->seconds();
    $component->call('submit')->assertHasNoErrors()->assertSet('sent', true);

    expect(ContactSubmission::query()->sole()->budget_range)->toBe('not-applicable');
    Mail::assertQueued(NewInquiryMail::class);
});

it('still requires a budget for project inquiries', function () {
    Livewire::test(ContactForm::class)->set('project_type', 'saas')->call('submit')
        ->assertHasErrors(['budget_range' => 'required']);
});

it('uses the uploaded logo in the header and generates favicons from it', function () {
    Storage::disk('public')->put('brand/logo.png', pngLogo());
    $design = app(DesignSettings::class);
    $design->logo_light = 'brand/logo.png';
    $design->save();
    Brand::regenerateIcons();

    expect(Storage::disk('public')->exists('brand/icons/favicon-32.png'))->toBeTrue()
        ->and(getimagesizefromstring((string) Storage::disk('public')->get('brand/icons/apple-touch-icon.png'))[0])->toBe(180);

    $this->get('/en')->assertOk()
        ->assertSee('storage/brand/logo.png', false)
        ->assertSee('dark:invert', false)
        ->assertSee('storage/brand/icons/favicon-32.png', false);
});

it('falls back to the built-in mark and default favicons without a logo', function () {
    $this->get('/en')->assertOk()->assertDontSee('storage/brand/', false)->assertSee('favicon.svg', false);
});

it('deletes replaced logo files', function () {
    Storage::disk('public')->put('brand/old.png', pngLogo());
    Storage::disk('public')->put('brand/new.png', pngLogo());
    $design = app(DesignSettings::class);
    $design->logo_light = 'brand/new.png';
    $design->save();

    Brand::prune();

    expect(Storage::disk('public')->exists('brand/old.png'))->toBeFalse()
        ->and(Storage::disk('public')->exists('brand/new.png'))->toBeTrue();
});
