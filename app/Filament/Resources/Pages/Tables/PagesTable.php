<?php

namespace App\Filament\Resources\Pages\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')->badge(),
                TextColumn::make('title'),
                TextColumn::make('updated_at')->since(),
            ])
            ->recordActions([EditAction::make()]);
    }
}
