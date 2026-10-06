<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Enums\EngagementType;
use App\Enums\SchemaType;
use App\Filament\Support\Bilingual;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Tabs::make('Project')->persistTabInQueryString()->tabs([
                self::identityTab(),
                self::visibilityTab(),
                self::caseStudyTab(),
                self::taxonomyTab(),
                self::mediaTab(),
                self::seoTab(),
            ]),
        ]);
    }

    private static function identityTab(): Tab
    {
        return Tab::make('Identity')->icon('heroicon-o-identification')->schema([
            Callout::make('Two identities per project')
                ->description('The site shows the anonymized title/summary until "Show client name" is on. Long-form fields (challenge, solution, architecture, features) are always public — keep them client-neutral. The leak check warns you on save if a client name slips in.')
                ->info(),
            Section::make('Anonymized (shown by default)')->schema([
                Bilingual::text('anonymized_title', 'Anonymized title', required: true),
                Bilingual::textarea('anonymized_summary', 'Anonymized summary', required: true, rows: 3, max: 400),
            ]),
            Section::make('Real identity (shown only when revealed)')->schema([
                Bilingual::text('title', 'Real title', required: true),
                Bilingual::textarea('summary', 'Real summary', required: true, rows: 3, max: 400),
                Bilingual::text('client_name', 'Client name'),
                TagsInput::make('client_aliases')
                    ->label('Client aliases (for the leak check)')
                    ->helperText('Other spellings, brand names and domains that must never appear while anonymized, e.g. "Petrogina", "petroginatrading".')
                    ->placeholder('Add alias'),
                Grid::make(['default' => 1, 'md' => 2])->schema([
                    TextInput::make('live_url')->label('Live URL')->url()->maxLength(255),
                    TextInput::make('repo_url')->label('Repository URL')->url()->maxLength(255),
                ]),
            ]),
            Section::make('Details')->schema([
                Grid::make(['default' => 1, 'md' => 3])->schema([
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(80)
                        ->alphaDash()
                        ->unique(ignoreRecord: true)
                        ->helperText('Neutral and permanent — never put a client name here.')
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (?string $state, callable $set) => $set('slug', Str::slug((string) $state)))
                        ->dehydrateStateUsing(fn (?string $state) => Str::slug((string) $state)),
                    Select::make('engagement_type')->options(collect(EngagementType::cases())->mapWithKeys(fn ($c) => [$c->value => ucfirst($c->value)]))->default(EngagementType::Client->value)->required(),
                    Select::make('schema_type')->label('Schema.org type')->options(collect(SchemaType::cases())->mapWithKeys(fn ($c) => [$c->value => $c->value]))->default(SchemaType::CreativeWork->value)->required(),
                ]),
                TextInput::make('year')->numeric()->minValue(2000)->maxValue(2100),
                Bilingual::text('industry', 'Industry'),
                Bilingual::text('role', 'My role'),
            ]),
        ]);
    }

    private static function visibilityTab(): Tab
    {
        return Tab::make('Visibility')->icon('heroicon-o-eye')->schema([
            Section::make('Publishing')->columns(['default' => 1, 'md' => 3])->schema([
                Toggle::make('is_published')->label('Published'),
                Toggle::make('is_featured')->label('Featured on home'),
                TextInput::make('sort_order')->numeric()->default(0),
            ]),
            Section::make('What the public may see')
                ->description('Everything is off by default (anonymized). Turn on only what the client has approved.')
                ->columns(['default' => 1, 'md' => 2])
                ->schema([
                    Toggle::make('show_client_name')->label('Client name & real title/summary'),
                    Toggle::make('show_live_link')->label('Link to the live site'),
                    Toggle::make('show_logo')->label('Client logo'),
                    Toggle::make('show_screenshots')->label('Screenshots gallery'),
                    Toggle::make('show_repo_link')->label('Repository link'),
                ]),
            Callout::make(fn (Get $get): string => collect(['show_client_name', 'show_live_link', 'show_logo', 'show_screenshots', 'show_repo_link'])->contains(fn ($f) => $get($f))
                ? 'Partially or fully revealed — make sure the client approved this.'
                : 'Anonymized: no client name, logo, domain or screenshots are public.')
                ->color(fn (Get $get): string => $get('show_client_name') || $get('show_screenshots') || $get('show_logo') ? 'warning' : 'success'),
        ]);
    }

    private static function caseStudyTab(): Tab
    {
        return Tab::make('Case study')->icon('heroicon-o-document-text')->schema([
            Bilingual::rich('challenge', 'Challenge'),
            Bilingual::rich('solution', 'Solution'),
            Bilingual::rich('architecture', 'Architecture'),
            Bilingual::rich('results', 'Results (verified outcomes only — leave empty until you have them)'),
            Repeater::make('features')
                ->relationship()
                ->orderColumn('sort_order')
                ->defaultItems(0)
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => $state['title']['en'] ?? null)
                ->schema([
                    Bilingual::text('title', 'Feature', required: true),
                    Bilingual::textarea('body', 'Description', rows: 2),
                ]),
            Repeater::make('facts')
                ->label('Verified facts')
                ->helperText('Numbers you can prove (test counts, languages, modules). Note the source for yourself.')
                ->relationship()
                ->orderColumn('sort_order')
                ->defaultItems(0)
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => trim(($state['value'] ?? '').' '.($state['label']['en'] ?? '')) ?: null)
                ->schema([
                    Grid::make(['default' => 1, 'md' => 3])->schema([
                        TextInput::make('value')->required()->maxLength(64),
                        TextInput::make('source_note')->label('Source (admin only)')->maxLength(255)->columnSpan(2),
                    ]),
                    Bilingual::text('label', 'Label', required: true),
                ]),
        ]);
    }

    private static function taxonomyTab(): Tab
    {
        return Tab::make('Taxonomy')->icon('heroicon-o-tag')->schema([
            Select::make('categories')->relationship('categories', 'slug')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('name', 'en'))
                ->multiple()->preload(),
            Select::make('technologies')->relationship('technologies', 'name')->multiple()->preload()->searchable(),
            Select::make('services')->relationship('services', 'slug')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('title', 'en'))
                ->multiple()->preload()
                ->helperText('Service pages list related projects from here.'),
        ]);
    }

    private static function mediaTab(): Tab
    {
        return Tab::make('Media')->icon('heroicon-o-photo')->schema([
            Section::make('Always public (must not identify the client)')->columns(['default' => 1, 'md' => 2])->schema([
                SpatieMediaLibraryFileUpload::make('cover')->collection('cover')->image()->maxSize(8192)
                    ->helperText('Optional. Without it, the site renders a generated plate.'),
                SpatieMediaLibraryFileUpload::make('architecture_diagram')->label('Architecture diagram')->collection('architecture')->image()->maxSize(8192),
            ]),
            Section::make('Private until revealed')
                ->description('Stored on a non-public disk. Files move to public storage only when the matching visibility toggle is on.')
                ->schema([
                    SpatieMediaLibraryFileUpload::make('logo')->collection('logo')->image()->visibility('private')->maxSize(4096),
                    SpatieMediaLibraryFileUpload::make('screenshots')->collection('screenshots')->image()->multiple()->reorderable()
                        ->visibility('private')->maxSize(12288)->maxFiles(24)->panelLayout('grid'),
                ]),
        ]);
    }

    private static function seoTab(): Tab
    {
        return Tab::make('SEO')->icon('heroicon-o-magnifying-glass')->schema([
            Callout::make('Optional overrides. When empty, the title and description come from the visible (anonymized or real) title and summary.')->info(),
            Bilingual::text('meta_title', 'Meta title', max: 70),
            Bilingual::textarea('meta_description', 'Meta description', rows: 2, max: 170),
        ]);
    }
}
