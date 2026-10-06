<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Identity — facts that are not yet confirmed (years of experience) stay null so copy omits them.
        $this->migrator->add('identity.person_name', ['en' => 'Omar Khaled', 'ar' => 'عمر خالد']);
        $this->migrator->add('identity.job_title', [
            'en' => 'Full-stack Laravel & Filament developer',
            'ar' => 'مطوّر Full-Stack متخصص في Laravel وFilament',
        ]);
        $this->migrator->add('identity.location', ['en' => 'Egypt', 'ar' => 'مصر']);
        $this->migrator->add('identity.country_code', 'EG');
        $this->migrator->add('identity.timezone', 'Africa/Cairo');
        $this->migrator->add('identity.working_hours', '10:00–19:00');
        $this->migrator->add('identity.availability_status', 'available');
        $this->migrator->add('identity.availability_note', [
            'en' => 'Available for new projects',
            'ar' => 'متاح لمشاريع جديدة',
        ]);
        $this->migrator->add('identity.response_time_hours', 24);
        $this->migrator->add('identity.years_experience', null);
        $this->migrator->add('identity.hero_headline', [
            'en' => 'Laravel platforms and back-offices for teams that need them to just work.',
            'ar' => 'أبني منصّات Laravel وأنظمة إدارة تعمل بثبات، لفرق العمل حول العالم.',
        ]);
        $this->migrator->add('identity.hero_subheadline', [
            'en' => 'I’m Omar Khaled, a full-stack Laravel and Filament developer in Egypt. I design and build e-commerce platforms, B2B portals, SaaS products and admin systems for companies worldwide — with a designer’s eye and automated tests.',
            'ar' => 'أنا عمر خالد، مطوّر Full-Stack متخصص في Laravel وFilament من مصر. أصمّم وأبني متاجر إلكترونية وبوابات B2B ومنتجات SaaS وأنظمة إدارة لشركات حول العالم، بعينِ مصمّم وبرمجةٍ مدعومة بالاختبارات الآلية.',
        ]);
        $this->migrator->add('identity.hero_cta_text', ['en' => 'Start a project', 'ar' => 'ابدأ مشروعك']);

        // Contact — clearly marked placeholders until real values are entered in the admin panel.
        $this->migrator->add('contact.contact_email', 'contact@example.com');
        $this->migrator->add('contact.upwork_url', 'https://www.upwork.com/freelancers/placeholder');
        $this->migrator->add('contact.linkedin_url', 'https://www.linkedin.com/in/omar-khaled-890b14396');
        $this->migrator->add('contact.github_url', 'https://github.com/OmarKhaled001');
        $this->migrator->add('contact.whatsapp', '+10000000000');
        $this->migrator->add('contact.behance_url', null);
        $this->migrator->add('contact.calendly_url', null);

        $this->migrator->add('design.accent_color', '#E8542A');

        $this->migrator->add('seo.indexing_enabled', true);
        $this->migrator->add('seo.google_site_verification', null);
        $this->migrator->add('seo.bing_site_verification', null);

        $this->migrator->add('spam.turnstile_enabled', false);
    }
};
