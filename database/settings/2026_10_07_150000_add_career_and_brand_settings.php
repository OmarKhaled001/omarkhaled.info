<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * D-038/D-039: the site speaks to clients and to employers (CV download, openness to roles),
 * and the logo can be replaced from Admin -> Settings -> Design.
 * Copy updates only replace untouched seeded defaults.
 */
return new class extends SettingsMigration
{
    private const array OLD_SUBHEADLINE = [
        'en' => 'I’m Omar Khaled, a full-stack Laravel and Filament developer in Egypt. E-commerce, B2B portals, SaaS and admin systems — designed, built and tested for teams worldwide.',
        'ar' => 'أنا عمر خالد، مطوّر Full-Stack متخصص في Laravel وFilament من مصر. متاجر إلكترونية وبوابات B2B ومنتجات SaaS وأنظمة إدارة، أصمّمها وأبنيها وأختبرها لفرق حول العالم.',
    ];

    private const array NEW_SUBHEADLINE = [
        'en' => 'I’m Omar Khaled, a full-stack Laravel and Filament developer in Egypt. I build e-commerce, B2B portals, SaaS and admin systems for clients — and I’m open to full-time and contract roles with teams worldwide.',
        'ar' => 'أنا عمر خالد، مطوّر Full-Stack متخصص في Laravel وFilament من مصر. أبني متاجر إلكترونية وبوابات B2B ومنتجات SaaS وأنظمة إدارة للعملاء، ومتاح أيضًا للعمل بدوام كامل أو بعقد مع فرق حول العالم.',
    ];

    private const array OLD_AVAILABILITY = ['en' => 'Available for new projects', 'ar' => 'متاح لمشاريع جديدة'];

    private const array NEW_AVAILABILITY = ['en' => 'Available for projects & roles', 'ar' => 'متاح للمشاريع والوظائف'];

    public function up(): void
    {
        $this->migrator->add('career.open_to_roles', true);
        $this->migrator->add('career.roles_note', [
            'en' => 'Open to full-time, contract and remote roles',
            'ar' => 'متاح للعمل بدوام كامل أو بعقد أو عن بُعد',
        ]);
        $this->migrator->add('career.cv_en', null);
        $this->migrator->add('career.cv_ar', null);

        $this->migrator->add('design.logo_light', null);
        $this->migrator->add('design.logo_dark', null);

        $this->migrator->update('identity.hero_subheadline', fn (mixed $value) => (array) $value == self::OLD_SUBHEADLINE ? self::NEW_SUBHEADLINE : $value);
        $this->migrator->update('identity.availability_note', fn (mixed $value) => (array) $value == self::OLD_AVAILABILITY ? self::NEW_AVAILABILITY : $value);
    }
};
