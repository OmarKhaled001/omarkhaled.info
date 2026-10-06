<?php

namespace App\Filament\Support;

use Closure;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;

/**
 * Side-by-side EN | AR inputs for a spatie/laravel-translatable attribute.
 * Filament reads/writes "field.en" / "field.ar" straight from the model's translation array.
 */
final class Bilingual
{
    /**
     * @param  Closure(string $locale): (TextInput|Textarea|RichEditor)  $make
     */
    public static function make(Closure $make, int $columns = 2): Grid
    {
        $fields = [];

        foreach (['en' => 'English', 'ar' => 'العربية'] as $locale => $language) {
            $field = $make($locale);
            // Resolve once: a closure calling getLabel() would re-enter itself forever.
            $field->label($field->getLabel().' · '.$language);

            if ($locale === 'ar') {
                $field->extraInputAttributes(['dir' => 'rtl', 'lang' => 'ar'], merge: true);
            }

            $fields[] = $field;
        }

        return Grid::make(['default' => 1, 'lg' => $columns])->schema($fields);
    }

    public static function text(string $name, string $label, bool $required = false, int $max = 255): Grid
    {
        return self::make(fn (string $locale) => TextInput::make("{$name}.{$locale}")
            ->label($label)
            ->maxLength($max)
            ->required($required));
    }

    public static function textarea(string $name, string $label, bool $required = false, int $rows = 3, ?int $max = null): Grid
    {
        return self::make(fn (string $locale) => Textarea::make("{$name}.{$locale}")
            ->label($label)
            ->rows($rows)
            ->maxLength($max)
            ->required($required));
    }

    public static function rich(string $name, string $label, bool $required = false): Grid
    {
        return self::make(fn (string $locale) => RichEditor::make("{$name}.{$locale}")
            ->label($label)
            ->toolbarButtons([['bold', 'italic', 'link'], ['h2', 'h3'], ['bulletList', 'orderedList', 'blockquote'], ['undo', 'redo']])
            ->required($required));
    }
}
