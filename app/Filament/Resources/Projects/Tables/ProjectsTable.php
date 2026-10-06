<?php

namespace App\Filament\Resources\Projects\Tables;

use App\Models\Project;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('anonymized_title')->label('Project')->searchable()->wrap()
                    ->description(fn (Project $record) => $record->getTranslation('client_name', 'en', false) ?: null),
                TextColumn::make('slug')->color('gray')->toggleable(),
                IconColumn::make('show_client_name')->label('Revealed')->boolean()
                    ->trueIcon('heroicon-o-eye')->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('warning')->falseColor('success'),
                ToggleColumn::make('is_published')->label('Published'),
                ToggleColumn::make('is_featured')->label('Featured'),
                TextColumn::make('year')->sortable(),
                TextColumn::make('updated_at')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_published'),
                TernaryFilter::make('is_featured'),
                TernaryFilter::make('show_client_name')->label('Revealed'),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
