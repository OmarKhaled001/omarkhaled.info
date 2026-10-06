<?php

namespace App\Filament\Resources\Faqs\Schemas;

use App\Filament\Support\Bilingual;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Callout::make('General FAQs appear on the home page. Service-specific FAQs are edited on each service.')->info(),
            Grid::make(['default' => 1, 'md' => 2])->schema([
                Toggle::make('is_published')->default(true),
                TextInput::make('sort_order')->numeric()->default(0),
            ]),
            Bilingual::text('question', 'Question', required: true),
            Bilingual::textarea('answer', 'Answer (first sentence = direct answer)', required: true, rows: 4),
        ]);
    }
}
