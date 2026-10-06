<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Enums\ServiceItemKind;
use App\Filament\Support\Bilingual;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Tabs::make('Service')->persistTabInQueryString()->tabs([
                Tab::make('Content')->schema([
                    Grid::make(['default' => 1, 'md' => 4])->schema([
                        TextInput::make('slug')->required()->alphaDash()->maxLength(80)->unique(ignoreRecord: true)
                            ->dehydrateStateUsing(fn (?string $state) => Str::slug((string) $state))->columnSpan(2),
                        TextInput::make('icon')->helperText('Lucide icon name, e.g. "server"')->maxLength(40),
                        TextInput::make('sort_order')->numeric()->default(0),
                    ]),
                    Toggle::make('is_published')->label('Published'),
                    Bilingual::text('title', 'Title', required: true),
                    Bilingual::textarea('card_summary', 'Card summary', required: true, rows: 2, max: 220),
                    Bilingual::text('headline', 'Page headline (H1)', required: true),
                    Bilingual::textarea('intro', 'Intro', required: true, rows: 3),
                    Bilingual::rich('problem', 'The problem'),
                    Bilingual::text('cta_text', 'CTA line'),
                ]),
                Tab::make('Deliverables & process')->schema([
                    Repeater::make('deliverables')
                        ->relationship('deliverables')
                        ->orderColumn('sort_order')
                        ->defaultItems(0)->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title']['en'] ?? null)
                        ->mutateRelationshipDataBeforeCreateUsing(fn (array $data) => $data + ['kind' => ServiceItemKind::Deliverable->value])
                        ->schema([
                            Bilingual::text('title', 'Deliverable', required: true),
                            Bilingual::textarea('body', 'Detail', rows: 2),
                        ]),
                    Repeater::make('processSteps')
                        ->label('Process steps')
                        ->relationship('processSteps')
                        ->orderColumn('sort_order')
                        ->defaultItems(0)->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title']['en'] ?? null)
                        ->mutateRelationshipDataBeforeCreateUsing(fn (array $data) => $data + ['kind' => ServiceItemKind::ProcessStep->value])
                        ->schema([
                            Bilingual::text('title', 'Step', required: true),
                            Bilingual::textarea('body', 'Detail', rows: 2),
                        ]),
                ]),
                Tab::make('FAQ')->schema([
                    Repeater::make('faqs')
                        ->relationship()
                        ->orderColumn('sort_order')
                        ->defaultItems(0)->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['question']['en'] ?? null)
                        ->schema([
                            Toggle::make('is_published')->default(true),
                            Bilingual::text('question', 'Question', required: true),
                            Bilingual::textarea('answer', 'Answer', required: true, rows: 4),
                        ]),
                ]),
                Tab::make('Related projects')->schema([
                    Select::make('projects')->relationship('projects', 'slug')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->getTranslation('anonymized_title', 'en'))
                        ->multiple()->preload(),
                ]),
                Tab::make('SEO')->schema([
                    Section::make()->schema([
                        Bilingual::text('meta_title', 'Meta title', max: 70),
                        Bilingual::textarea('meta_description', 'Meta description', rows: 2, max: 170),
                    ]),
                ]),
            ]),
        ]);
    }
}
