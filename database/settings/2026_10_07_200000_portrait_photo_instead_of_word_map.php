<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/** D-042: the About page shows the portrait as a photo; the word-portrait light map is gone. */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->delete('identity.portrait_map');
        $this->migrator->add('identity.portrait_images', null);
    }
};
