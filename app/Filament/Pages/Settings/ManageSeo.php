<?php

namespace App\Filament\Pages\Settings;

use App\Settings\SeoSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageSeo extends SettingsPage
{
    protected static string $settings = SeoSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'SEO';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'SEO';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Toggle::make('indexing_enabled')->label('Allow search engines and AI crawlers to index the site')
                ->helperText('Turn off on staging: every page becomes noindex and robots.txt disallows everything.'),
            TextInput::make('google_site_verification')->label('Google Search Console verification code')
                ->helperText('Only the content value of the meta tag.')->maxLength(120),
            TextInput::make('bing_site_verification')->label('Bing Webmaster verification code')->maxLength(120),
        ]);
    }
}
