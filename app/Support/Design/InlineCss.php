<?php

namespace App\Support\Design;

use Illuminate\Support\Facades\Vite;

/**
 * The built stylesheet (~6 KB brotli) is inlined into <head>: it removes a render-blocking request
 * from the critical path (LCP). The CSP allows it by SHA-256. Deploys must clear the response cache
 * so cached HTML never carries an old stylesheet whose hash no longer matches.
 */
final class InlineCss
{
    private static ?string $css = null;

    private static bool $resolved = false;

    public static function css(): ?string
    {
        if (self::$resolved) {
            return self::$css;
        }

        self::$resolved = true;
        $manifest = public_path('build/manifest.json');

        if (Vite::isRunningHot() || ! is_file($manifest)) {
            return null;
        }

        $entry = json_decode((string) file_get_contents($manifest), true)['resources/css/app.css']['file'] ?? null;
        $path = $entry ? public_path('build/'.$entry) : null;

        return self::$css = ($path && is_file($path)) ? trim((string) file_get_contents($path)) : null;
    }

    public static function hash(): ?string
    {
        $css = self::css();

        return $css === null ? null : "'sha256-".base64_encode(hash('sha256', $css, true))."'";
    }

    /** For tests and long-running workers after a rebuild. */
    public static function flush(): void
    {
        self::$css = null;
        self::$resolved = false;
    }
}
