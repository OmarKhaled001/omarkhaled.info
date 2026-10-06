<?php

namespace App\Filament\Resources\Technologies\Tables;

use App\Enums\TechnologyDomain;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TechnologiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('domain')->badge()->formatStateUsing(fn (TechnologyDomain $state) => $state->value),
                TextColumn::make('projects_count')->counts('projects')->label('Projects'),
                ToggleColumn::make('show_on_about')->label('About'),
                ToggleColumn::make('show_in_stack')->label('Home stack'),
            ])
            ->filters([SelectFilter::make('domain')->options(collect(TechnologyDomain::cases())->mapWithKeys(fn ($c) => [$c->value => $c->value]))])
            ->recordActions([EditAction::make()]);
    }
}
