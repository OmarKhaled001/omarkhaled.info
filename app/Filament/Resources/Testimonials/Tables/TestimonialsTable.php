<?php

namespace App\Filament\Resources\Testimonials\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TestimonialsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('author_name')->searchable(),
                TextColumn::make('quote')->limit(80)->wrap(),
                IconColumn::make('is_placeholder')->label('Placeholder')->boolean()->trueColor('warning'),
                IconColumn::make('is_published')->label('Published')->boolean(),
            ])
            ->recordActions([EditAction::make()]);
    }
}
