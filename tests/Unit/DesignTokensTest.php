<?php

use App\Support\Design\Color;

/** @return array<string, string> */
function themeTokens(string $theme): array
{
    $css = file_get_contents(dirname(__DIR__, 2).'/resources/css/app.css');
    preg_match("#/\\* tokens:{$theme} \\*/(.*?)/\\* /tokens:{$theme} \\*/#s", $css, $block);
    preg_match_all('/--([a-z0-9-]+):\s*(#[0-9A-Fa-f]{6})\s*;/', $block[1] ?? '', $matches, PREG_SET_ORDER);

    return collect($matches)->mapWithKeys(fn ($m) => [$m[1] => $m[2]])->all();
}

it('meets WCAG AA contrast for every text/background pair', function (string $theme) {
    $t = themeTokens($theme);
    expect($t)->not->toBeEmpty();

    foreach (['bg', 'surface', 'surface-2'] as $background) {
        foreach (['text', 'muted', 'accent-text'] as $foreground) {
            $ratio = Color::hex($t[$foreground])->contrastWith(Color::hex($t[$background]));
            expect($ratio)->toBeGreaterThanOrEqual(4.5, "{$theme}: {$foreground} on {$background} is {$ratio}");
        }

        $focus = Color::hex($t['focus'])->contrastWith(Color::hex($t[$background]));
        expect($focus)->toBeGreaterThanOrEqual(3.0, "{$theme}: focus on {$background}");
    }

    expect(Color::hex($t['on-accent'])->contrastWith(Color::hex($t['accent'])))->toBeGreaterThanOrEqual(4.5);
})->with(['light', 'dark']);

it('keeps the dark media-query block identical to the data-theme block', function () {
    $css = file_get_contents(dirname(__DIR__, 2).'/resources/css/app.css');
    preg_match("#:root\\[data-theme='dark'\\] \\{(.*?)\\}#s", $css, $explicit);
    preg_match("#:root:not\\(\\[data-theme='light'\\]\\) \\{(.*?)\\}#s", $css, $media);

    $normalize = fn (string $s) => preg_replace('/\s+/', '', $s);
    expect($normalize($media[1]))->toBe($normalize($explicit[1]));
});
