<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Support\Bilingual;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            TextInput::make('key')->required()->alphaDash()->unique(ignoreRecord: true)->disabledOn('edit')
                ->helperText('Used by the code: "about" or "privacy".'),
            Bilingual::text('title', 'Title', required: true),
            Bilingual::rich('body', 'Body', required: true),
            Bilingual::text('meta_title', 'Meta title', max: 70),
            Bilingual::textarea('meta_description', 'Meta description', rows: 2, max: 170),
        ]);
    }
}
