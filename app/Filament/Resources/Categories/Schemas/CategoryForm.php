<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Filament\Support\Bilingual;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            TextInput::make('slug')->required()->alphaDash()->maxLength(60)->unique(ignoreRecord: true),
            Bilingual::text('name', 'Name', required: true),
            TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }
}
