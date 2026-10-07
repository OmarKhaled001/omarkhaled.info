<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Shorter hero copy for the 3D hero (D-037). Only replaces the original seeded defaults:
 * anything already edited in Admin -> Settings -> Identity is left untouched.
 */
return new class extends SettingsMigration
{
    private const array OLD_HEADLINE = [
        'en' => 'Laravel platforms and back-offices for teams that need them to just work.',
        'ar' => 'أبني منصّات Laravel وأنظمة إدارة تعمل بثبات، لفرق العمل حول العالم.',
    ];

    // *Asterisks* mark the words shown in the accent colour (App\Support\Profile::heroHeadlineHtml).
    private const array NEW_HEADLINE = [
        'en' => 'Laravel platforms that *just work*.',
        'ar' => 'منصّات Laravel *تعمل بثبات*.',
    ];

    private const array OLD_SUBHEADLINE = [
        'en' => 'I’m Omar Khaled, a full-stack Laravel and Filament developer in Egypt. I design and build e-commerce platforms, B2B portals, SaaS products and admin systems for companies worldwide — with a designer’s eye and automated tests.',
        'ar' => 'أنا عمر خالد، مطوّر Full-Stack متخصص في Laravel وFilament من مصر. أصمّم وأبني متاجر إلكترونية وبوابات B2B ومنتجات SaaS وأنظمة إدارة لشركات حول العالم، بعينِ مصمّم وبرمجةٍ مدعومة بالاختبارات الآلية.',
    ];

    private const array NEW_SUBHEADLINE = [
        'en' => 'I’m Omar Khaled, a full-stack Laravel and Filament developer in Egypt. E-commerce, B2B portals, SaaS and admin systems — designed, built and tested for teams worldwide.',
        'ar' => 'أنا عمر خالد، مطوّر Full-Stack متخصص في Laravel وFilament من مصر. متاجر إلكترونية وبوابات B2B ومنتجات SaaS وأنظمة إدارة، أصمّمها وأبنيها وأختبرها لفرق حول العالم.',
    ];

    public function up(): void
    {
        $this->migrator->update('identity.hero_headline', fn (mixed $value) => (array) $value == self::OLD_HEADLINE ? self::NEW_HEADLINE : $value);
        $this->migrator->update('identity.hero_subheadline', fn (mixed $value) => (array) $value == self::OLD_SUBHEADLINE ? self::NEW_SUBHEADLINE : $value);
    }
};
