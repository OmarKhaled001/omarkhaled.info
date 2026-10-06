<?php

namespace App\Support;

/** Decides whether a setting still holds a seed placeholder (or nothing at all). */
final class PlaceholderDetector
{
    public static function isPlaceholder(mixed $value): bool
    {
        if (is_array($value)) {
            // A translatable value is a placeholder if any locale is.
            return $value === [] || collect($value)->contains(fn ($v): bool => self::isPlaceholder($v));
        }

        if ($value === null || (is_string($value) && trim($value) === '')) {
            return true;
        }

        if (! is_string($value)) {
            return false;
        }

        $normalized = mb_strtolower(trim($value));

        foreach (config('portfolio.placeholders.exact', []) as $exact) {
            if ($normalized === mb_strtolower($exact)) {
                return true;
            }
        }

        foreach (config('portfolio.placeholders.contains', []) as $needle) {
            if (str_contains($normalized, mb_strtolower($needle))) {
                return true;
            }
        }

        return false;
    }

    /** The value if it is real, otherwise null. */
    public static function real(mixed $value): mixed
    {
        return self::isPlaceholder($value) ? null : $value;
    }
}
