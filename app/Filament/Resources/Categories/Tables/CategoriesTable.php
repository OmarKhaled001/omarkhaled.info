<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('slug')->color('gray'),
                TextColumn::make('projects_count')->counts('projects')->label('Projects'),
            ])
            ->recordActions([EditAction::make()]);
    }
}
