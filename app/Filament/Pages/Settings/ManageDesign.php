<?php

namespace App\Filament\Pages\Settings;

use App\Settings\DesignSettings;
use App\Support\Design\AccentPalette;
use App\Support\Design\Color;
use BackedEnum;
use Filament\Forms\Components\ColorPicker;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageDesign extends SettingsPage
{
    protected static string $settings = DesignSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Design';

    protected static ?int $navigationSort = 3;

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            ColorPicker::make('accent_color')->label('Accent colour')->hex()->required()->live()
                ->rule(fn () => fn (string $attribute, mixed $value, \Closure $fail) => Color::isValidHex($value) ? null : $fail('Use a hex colour like #E8542A.'))
                ->helperText('Default: '.AccentPalette::DEFAULT.' (vermilion). Text, button-label and focus variants are derived automatically so contrast stays WCAG AA in light and dark mode.'),
            Callout::make(function (Get $get): string {
                $p = AccentPalette::from($get('accent_color'));

                return "Derived tokens — light: fill {$p->lightFill}, text {$p->lightText}, label on fill {$p->lightOnFill} · dark: fill {$p->darkFill}, text {$p->darkText}, label on fill {$p->darkOnFill}.";
            })->info(),
        ]);
    }
}
