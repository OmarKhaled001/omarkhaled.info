<?php

namespace App\Filament\Pages\Settings;

use App\Settings\ContactSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageContact extends SettingsPage
{
    protected static string $settings = ContactSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAtSymbol;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Contact & social';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Contact & social';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Callout::make('Placeholder values (example.com, /placeholder, +10000000000) are never shown publicly and never appear in structured data, the sitemap or llms.txt. The dashboard launch checklist lists what is still missing.')->info(),
            Section::make('Email')->schema([
                TextInput::make('contact_email')->email()->required()
                    ->helperText('Shown on the contact page and used as the inquiry recipient (falls back to MAIL_CONTACT_ADDRESS while this is a placeholder).'),
            ]),
            Section::make('Profiles')->description('Also used as schema.org sameAs links — keep them identical to your real profiles.')->columns(['default' => 1, 'md' => 2])->schema([
                TextInput::make('linkedin_url')->label('LinkedIn')->url()->prefixIcon('heroicon-o-link'),
                TextInput::make('github_url')->label('GitHub')->url()->prefixIcon('heroicon-o-link'),
                TextInput::make('upwork_url')->label('Upwork')->url()->prefixIcon('heroicon-o-link'),
                TextInput::make('behance_url')->label('Behance (optional)')->url()->prefixIcon('heroicon-o-link'),
                TextInput::make('calendly_url')->label('Booking link (optional)')->url()->prefixIcon('heroicon-o-calendar'),
                TextInput::make('whatsapp')->label('WhatsApp (international format)')->tel()->placeholder('+20…'),
            ]),
        ]);
    }
}
