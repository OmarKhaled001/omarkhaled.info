<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use App\Filament\Support\Bilingual;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Callout::make('Testimonials stay hidden until "Placeholder" is off and "Published" is on. Only publish words a real client gave you permission to use.')->warning(),
            Grid::make(['default' => 1, 'md' => 3])->schema([
                Toggle::make('is_placeholder')->label('Placeholder')->default(true)->live(),
                Toggle::make('is_published')->label('Published')
                    ->disabled(fn (Get $get): bool => (bool) $get('is_placeholder'))
                    ->helperText(fn (Get $get): ?string => $get('is_placeholder') ? 'Turn off "Placeholder" first.' : null),
                TextInput::make('sort_order')->numeric()->default(0),
            ]),
            TextInput::make('author_name')->required()->maxLength(120),
            Bilingual::text('author_role', 'Role'),
            Bilingual::text('company', 'Company'),
            Bilingual::textarea('quote', 'Quote', required: true, rows: 4, max: 600),
            Select::make('project_id')->relationship('project', 'slug')->label('Related project')->preload(),
            SpatieMediaLibraryFileUpload::make('avatar')->collection('avatar')->image()->avatar()->maxSize(2048),
        ]);
    }
}
