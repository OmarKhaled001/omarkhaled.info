<?php

namespace App\Filament\Resources\Experiences\Schemas;

use App\Filament\Support\Bilingual;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ExperienceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Grid::make(['default' => 1, 'md' => 3])->schema([
                Toggle::make('is_published'),
                Toggle::make('is_current')->label('Current role'),
                TextInput::make('sort_order')->numeric()->default(0),
            ]),
            TextInput::make('company')->required()->maxLength(120),
            Bilingual::text('role', 'Role', required: true),
            Grid::make(['default' => 1, 'md' => 2])->schema([
                DatePicker::make('started_on')->native(false)->displayFormat('M Y'),
                DatePicker::make('ended_on')->native(false)->displayFormat('M Y'),
            ]),
            Bilingual::textarea('description', 'Description', rows: 3),
        ]);
    }
}
