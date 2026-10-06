<?php

use App\Support\Design\AccentPalette;
use App\Support\Design\Color;

$contrast = fn (string $a, string $b): float => Color::hex($a)->contrastWith(Color::hex($b));

it('keeps every derived accent token readable for any base colour', function (string $base) use ($contrast) {
    $p = AccentPalette::from($base);

    foreach (AccentPalette::LIGHT_SURFACES as $surface) {
        expect($contrast($p->lightText, $surface))->toBeGreaterThanOrEqual(4.5);
    }
    foreach (AccentPalette::DARK_SURFACES as $surface) {
        expect($contrast($p->darkText, $surface))->toBeGreaterThanOrEqual(4.5)
            ->and($contrast($p->darkFill, $surface))->toBeGreaterThanOrEqual(3.0);
    }

    expect($contrast($p->lightFill, $p->lightOnFill))->toBeGreaterThanOrEqual(4.5)
        ->and($contrast($p->darkFill, $p->darkOnFill))->toBeGreaterThanOrEqual(4.5);
})->with(['#E8542A', '#FFD400', '#1D4ED8', '#F9A8D4', '#000000', '#FFFFFF', '#22C55E', '#7C3AED']);

it('falls back to the default vermilion for invalid input', function (?string $input) {
    expect(AccentPalette::from($input)->base)->toBe(AccentPalette::DEFAULT);
})->with([null, '', 'red', '#12', 'javascript:alert(1)']);

it('emits css for both themes with the same selectors as app.css', function () {
    $css = AccentPalette::from('#E8542A')->css();

    expect($css)->toContain(':root{--accent:#E8542A')
        ->toContain('@media (prefers-color-scheme:dark){:root:not([data-theme=light]){')
        ->toContain(':root[data-theme=dark]{');
});
