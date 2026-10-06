<?php

namespace App\Filament\Pages\Settings;

use App\Settings\SpamSettings;
use BackedEnum;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageSpam extends SettingsPage
{
    protected static string $settings = SpamSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Spam protection';

    protected static ?int $navigationSort = 5;

    public function form(Schema $schema): Schema
    {
        $configured = filled(config('portfolio.turnstile.site_key')) && filled(config('portfolio.turnstile.secret_key'));

        return $schema->columns(1)->components([
            Callout::make('The contact form always uses a honeypot, a time-trap and per-IP/per-email rate limits. Cloudflare Turnstile is an optional extra layer.')->info(),
            Toggle::make('turnstile_enabled')->label('Enable Cloudflare Turnstile')
                ->disabled(! $configured)
                ->helperText($configured ? 'Keys found in .env.' : 'Add TURNSTILE_SITE_KEY and TURNSTILE_SECRET_KEY to .env first.'),
        ]);
    }
}
