<?php

namespace App\Filament\Resources\Experiences\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ExperiencesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('company'),
                TextColumn::make('role'),
                TextColumn::make('started_on')->date('M Y')->placeholder('—'),
                ToggleColumn::make('is_published')->label('Published'),
            ])
            ->recordActions([EditAction::make()]);
    }
}
