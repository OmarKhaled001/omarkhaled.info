<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Support\Bilingual;
use App\Settings\IdentitySettings;
use App\Support\Design\Portrait;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageIdentity extends SettingsPage
{
    protected static string $settings = IdentitySettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Identity & hero';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Identity & hero';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Who you are')->description('Used consistently in page copy, JSON-LD (Person), llms.txt and OG images.')->schema([
                Bilingual::text('person_name', 'Name', required: true),
                Bilingual::text('job_title', 'Job title', required: true),
                Bilingual::text('location', 'Location', required: true),
                Grid::make(['default' => 1, 'md' => 3])->schema([
                    TextInput::make('country_code')->label('Country (ISO code)')->length(2)->required(),
                    Select::make('timezone')->options(collect(timezone_identifiers_list())->mapWithKeys(fn ($tz) => [$tz => $tz]))->searchable()->required(),
                    TextInput::make('working_hours')->helperText('Local time, e.g. 10:00–19:00')->required(),
                ]),
                TextInput::make('years_experience')->numeric()->minValue(1)->maxValue(50)
                    ->helperText('Leave empty and the site never states a number.'),
            ]),
            Section::make('Availability')->schema([
                Grid::make(['default' => 1, 'md' => 2])->schema([
                    Select::make('availability_status')->options([
                        'available' => 'Available for new projects',
                        'limited' => 'Limited availability',
                        'booked' => 'Fully booked',
                    ])->required(),
                    TextInput::make('response_time_hours')->label('Reply within (hours)')->numeric()->minValue(1)->maxValue(168)->required(),
                ]),
                Bilingual::text('availability_note', 'Availability note'),
            ]),
            Section::make('Portrait (About page)')
                ->description('A photo of you on a dark background works best (face lit, background black). The photo itself stays private: the About page only receives a small grayscale light map and draws it with words. Remove it to hide the section.')
                ->schema([
                    FileUpload::make('portrait')
                        ->label('Portrait photo')
                        ->disk(Portrait::SOURCE_DISK)
                        ->directory(Portrait::DIRECTORY)
                        ->visibility('private')
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(8192),
                ]),
            Section::make('Home hero')->schema([
                Bilingual::textarea('hero_headline', 'Headline (H1)', required: true, rows: 2, max: 140, hint: 'Keep it short. Wrap words in *asterisks* to show them in the accent colour.'),
                Bilingual::textarea('hero_subheadline', 'Supporting line', required: true, rows: 3, max: 400),
                Bilingual::text('hero_cta_text', 'Primary button', required: true, max: 40),
            ]),
        ]);
    }

    protected function afterSave(): void
    {
        Portrait::regenerate();
    }
}
