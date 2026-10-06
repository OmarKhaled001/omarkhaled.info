<?php

namespace App\Support\Design;

/**
 * Derives every accent token from one base colour so an accent picked in
 * Site Settings can never break WCAG AA contrast in either theme.
 */
final readonly class AccentPalette
{
    public const string DEFAULT = '#E8542A';

    /** Theme surfaces the accent must stay readable on. Mirrors resources/css/app.css. */
    public const array LIGHT_SURFACES = ['#F7F6F2', '#FFFFFF', '#EFEDE7'];

    public const array DARK_SURFACES = ['#0A0A0B', '#111113', '#17171A'];

    public const string INK = '#131316';

    public const string PAPER = '#FFFFFF';

    public function __construct(
        public string $base,
        public string $lightFill,
        public string $lightText,
        public string $lightOnFill,
        public string $darkFill,
        public string $darkText,
        public string $darkOnFill,
    ) {}

    public static function from(?string $hex): self
    {
        $base = Color::isValidHex($hex) ? Color::hex($hex) : Color::hex(self::DEFAULT);

        $darkFill = self::ensureContrast($base, self::DARK_SURFACES, 3.0, +1);

        return new self(
            base: $base->toHex(),
            lightFill: $base->toHex(),
            lightText: self::ensureContrast($base, self::LIGHT_SURFACES, 4.5, -1)->toHex(),
            lightOnFill: self::bestOn($base)->toHex(),
            darkFill: $darkFill->toHex(),
            darkText: self::ensureContrast($base, self::DARK_SURFACES, 4.5, +1)->toHex(),
            darkOnFill: self::bestOn($darkFill)->toHex(),
        );
    }

    /**
     * @param  list<string>  $surfaces
     */
    private static function ensureContrast(Color $color, array $surfaces, float $ratio, int $direction): Color
    {
        $candidate = $color;

        for ($i = 0; $i < 100; $i++) {
            $min = min(array_map(fn (string $s): float => $candidate->contrastWith(Color::hex($s)), $surfaces));

            if ($min >= $ratio) {
                return $candidate;
            }

            $candidate = $candidate->shiftLightness($direction * 0.01);
        }

        return $candidate;
    }

    /** Text colour for labels sitting on the accent fill. */
    private static function bestOn(Color $fill): Color
    {
        $ink = Color::hex(self::INK);
        $paper = Color::hex(self::PAPER);

        return $fill->contrastWith($ink) >= $fill->contrastWith($paper) ? $ink : $paper;
    }

    /** CSS custom properties for both themes, mirroring the selectors in app.css. */
    public function css(): string
    {
        $light = "--accent:{$this->lightFill};--accent-text:{$this->lightText};--on-accent:{$this->lightOnFill};--focus:{$this->lightText}";
        $dark = "--accent:{$this->darkFill};--accent-text:{$this->darkText};--on-accent:{$this->darkOnFill};--focus:{$this->darkText}";

        return ":root{{$light}}"
            ."@media (prefers-color-scheme:dark){:root:not([data-theme=light]){{$dark}}}"
            .":root[data-theme=dark]{{$dark}}";
    }
}
