<?php

namespace App\Filament\Pages\Settings;

use App\Filament\Support\Bilingual;
use App\Settings\CareerSettings;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

/** The employer-facing side of the site: openness to roles and the downloadable CV. */
class ManageCareer extends SettingsPage
{
    protected static string $settings = CareerSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Hiring & CV';

    protected static ?string $title = 'Hiring & CV';

    protected static ?int $navigationSort = 2;

    private const string DIRECTORY = 'cv';

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Open to roles')
                ->description('Shows the hiring note in the hero, the "For companies hiring" card and the "Job or contract role" inquiry type.')
                ->schema([
                    Toggle::make('open_to_roles')->label('Open to full-time / contract roles'),
                    Bilingual::text('roles_note', 'Hiring note', max: 120),
                ]),
            Section::make('CV')
                ->description('PDF, up to 5 MB. Visitors download it from /en/cv and /ar/cv as "omar-khaled-CV-EN.pdf". The download buttons only appear once a CV is uploaded; Arabic pages fall back to the English CV.')
                ->schema([
                    Grid::make(2)->schema([
                        $this->cvUpload('cv_en', 'CV (English)'),
                        $this->cvUpload('cv_ar', 'CV (Arabic, optional)'),
                    ]),
                ]),
        ]);
    }

    private function cvUpload(string $name, string $label): FileUpload
    {
        return FileUpload::make($name)
            ->label($label)
            ->disk('public')
            ->directory(self::DIRECTORY)
            ->visibility('public')
            ->acceptedFileTypes(['application/pdf'])
            ->maxSize(5120)
            ->downloadable()
            ->openable();
    }

    /** Replaced or removed CVs are deleted, so old versions never stay downloadable. */
    protected function afterSave(): void
    {
        $settings = app(CareerSettings::class);
        $keep = array_filter([$settings->cv_en, $settings->cv_ar]);
        $disk = Storage::disk('public');

        foreach ($disk->files(self::DIRECTORY) as $file) {
            if (! in_array($file, $keep, true)) {
                $disk->delete($file);
            }
        }
    }
}
