<?php

namespace App\Support\Design;

use InvalidArgumentException;

/**
 * Minimal sRGB colour value used to derive accessible design tokens.
 */
final readonly class Color
{
    public function __construct(
        public float $r,
        public float $g,
        public float $b,
    ) {}

    public static function hex(string $hex): self
    {
        $hex = ltrim(trim($hex), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            throw new InvalidArgumentException("Invalid hex colour [{$hex}].");
        }

        return new self(
            hexdec(substr($hex, 0, 2)) / 255,
            hexdec(substr($hex, 2, 2)) / 255,
            hexdec(substr($hex, 4, 2)) / 255,
        );
    }

    public static function isValidHex(?string $hex): bool
    {
        return is_string($hex) && preg_match('/^#?([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', trim($hex)) === 1;
    }

    public function toHex(): string
    {
        return sprintf(
            '#%02X%02X%02X',
            (int) round($this->r * 255),
            (int) round($this->g * 255),
            (int) round($this->b * 255),
        );
    }

    /** WCAG 2.x relative luminance. */
    public function luminance(): float
    {
        $channel = static fn (float $c): float => $c <= 0.04045 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;

        return 0.2126 * $channel($this->r) + 0.7152 * $channel($this->g) + 0.0722 * $channel($this->b);
    }

    public function contrastWith(self $other): float
    {
        $a = $this->luminance();
        $b = $other->luminance();

        return (max($a, $b) + 0.05) / (min($a, $b) + 0.05);
    }

    /** Move towards black (negative) or white (positive) in HSL lightness, keeping hue. */
    public function shiftLightness(float $delta): self
    {
        [$h, $s, $l] = $this->toHsl();

        return self::fromHsl($h, $s, max(0.0, min(1.0, $l + $delta)));
    }

    /** @return array{0: float, 1: float, 2: float} */
    public function toHsl(): array
    {
        $max = max($this->r, $this->g, $this->b);
        $min = min($this->r, $this->g, $this->b);
        $l = ($max + $min) / 2;

        if ($max === $min) {
            return [0.0, 0.0, $l];
        }

        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

        $h = match ($max) {
            $this->r => fmod(($this->g - $this->b) / $d + ($this->g < $this->b ? 6 : 0), 6),
            $this->g => ($this->b - $this->r) / $d + 2,
            default => ($this->r - $this->g) / $d + 4,
        };

        return [$h / 6, $s, $l];
    }

    public static function fromHsl(float $h, float $s, float $l): self
    {
        if ($s === 0.0) {
            return new self($l, $l, $l);
        }

        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;

        $hue = static function (float $t) use ($p, $q): float {
            $t = $t < 0 ? $t + 1 : ($t > 1 ? $t - 1 : $t);

            return match (true) {
                $t < 1 / 6 => $p + ($q - $p) * 6 * $t,
                $t < 1 / 2 => $q,
                $t < 2 / 3 => $p + ($q - $p) * (2 / 3 - $t) * 6,
                default => $p,
            };
        };

        return new self($hue($h + 1 / 3), $hue($h), $hue($h - 1 / 3));
    }
}
