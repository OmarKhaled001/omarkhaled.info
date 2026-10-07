<?php

use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Models\Project;
use App\Models\Testimonial;
use App\Models\User;
use Livewire\Livewire;

function admin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['is_admin' => true, 'app_authentication_secret' => 'JBSWY3DPEHPK3PXP'])->save();

    return $user;
}

it('sends guests to the login page', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('forbids authenticated users who are not admins', function () {
    $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
});

it('forces admins without MFA to set up an authenticator app first', function () {
    $user = User::factory()->create();
    $user->forceFill(['is_admin' => true])->save();

    $this->actingAs($user)->get('/admin')->assertRedirectContains('multi-factor-authentication/set-up');
});

it('marks the panel noindex', function () {
    $this->get('/admin/login')->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
});

it('renders every admin screen for an admin', function (string $path) {
    $this->actingAs(admin())->get($path)->assertOk();
})->with([
    '/admin', '/admin/projects', '/admin/projects/create', '/admin/services', '/admin/services/create',
    '/admin/categories', '/admin/technologies', '/admin/testimonials', '/admin/faqs', '/admin/experiences',
    '/admin/pages', '/admin/contact-submissions', '/admin/manage-identity', '/admin/manage-contact',
    '/admin/manage-career', '/admin/manage-design', '/admin/manage-seo', '/admin/manage-spam',
]);

it('shows placeholder settings on the dashboard launch checklist', function () {
    $this->actingAs(admin())->get('/admin')
        ->assertSee('Launch checklist')
        ->assertSee('Contact email')
        ->assertSee('Upwork profile URL');
});

it('saves both locales of a project through the admin form', function () {
    $this->actingAs(admin());

    Livewire::test(CreateProject::class)
        ->fillForm([
            'anonymized_title' => ['en' => 'Booking platform for a travel company', 'ar' => 'منصة حجوزات لشركة سياحة'],
            'anonymized_summary' => ['en' => 'Summary', 'ar' => 'ملخص'],
            'title' => ['en' => 'Real title', 'ar' => 'العنوان الحقيقي'],
            'summary' => ['en' => 'Real summary', 'ar' => 'الملخص الحقيقي'],
            'slug' => 'travel-booking-platform',
            'engagement_type' => 'client',
            'schema_type' => 'CreativeWork',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $project = Project::query()->where('slug', 'travel-booking-platform')->firstOrFail();

    expect($project->getTranslation('anonymized_title', 'ar'))->toBe('منصة حجوزات لشركة سياحة')
        ->and($project->show_client_name)->toBeFalse()
        ->and($project->is_published)->toBeFalse();
});

it('never lets a placeholder testimonial be published', function () {
    $t = Testimonial::query()->create([
        'author_name' => 'Jane Doe', 'quote' => ['en' => '[placeholder]'], 'is_placeholder' => true, 'is_published' => true,
    ]);

    expect($t->fresh()->is_published)->toBeFalse()
        ->and(Testimonial::query()->visible()->count())->toBe(0);
});
