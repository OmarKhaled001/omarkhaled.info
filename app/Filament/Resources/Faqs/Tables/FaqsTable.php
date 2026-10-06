<?php

namespace App\Filament\Resources\Faqs\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FaqsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('faqable_type'))
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('question')->wrap()->searchable(),
                ToggleColumn::make('is_published')->label('Published'),
            ])
            ->recordActions([EditAction::make()]);
    }
}
