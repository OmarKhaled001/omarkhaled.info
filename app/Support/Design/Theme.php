<?php

namespace App\Support\Design;

/**
 * Inline <head> assets. Both are hashed into the CSP (App\Http\Middleware\SecurityHeaders),
 * which is why they must be produced only here and printed verbatim.
 */
final class Theme
{
    /** Applies a saved light/dark choice before first paint, so there is no theme flash. */
    public static function script(): string
    {
        return "(function(){try{var t=localStorage.getItem('theme');if(t==='light'||t==='dark'){document.documentElement.dataset.theme=t}}catch(e){}})();";
    }

    /** Accent tokens derived from the accent colour in Site Settings. */
    public static function accentCss(): string
    {
        return AccentPalette::from(self::accentHex())->css();
    }

    public static function accentHex(): string
    {
        $resolver = app()->bound('portfolio.accent') ? app('portfolio.accent') : null;

        return is_callable($resolver) ? (string) $resolver() : AccentPalette::DEFAULT;
    }

    /** CSP source expressions for the inline assets above. */
    public static function scriptHash(): string
    {
        return "'sha256-".base64_encode(hash('sha256', self::script(), true))."'";
    }

    public static function styleHash(): string
    {
        return "'sha256-".base64_encode(hash('sha256', self::accentCss(), true))."'";
    }
}
