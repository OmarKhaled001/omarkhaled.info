<?php

namespace App\Filament\Resources\Technologies\Schemas;

use App\Enums\TechnologyDomain;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class TechnologyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Grid::make(['default' => 1, 'md' => 2])->schema([
                TextInput::make('name')->required()->maxLength(60),
                TextInput::make('slug')->required()->alphaDash()->maxLength(60)->unique(ignoreRecord: true),
                Select::make('domain')->options(collect(TechnologyDomain::cases())->mapWithKeys(fn ($c) => [$c->value => ucfirst(str_replace('-', ' ', $c->value))]))->required(),
                TextInput::make('url')->url()->maxLength(255),
                TextInput::make('sort_order')->numeric()->default(0),
            ]),
            Toggle::make('show_on_about')->label('List under skills on the About page')->default(true),
            Toggle::make('show_in_stack')->label('Show in the home page stack row'),
        ]);
    }
}
