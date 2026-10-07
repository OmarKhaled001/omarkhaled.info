<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/** D-040: the About page's typographic portrait (photo upload + generated light map). */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('identity.portrait', null);
        $this->migrator->add('identity.portrait_map', null);
    }
};
