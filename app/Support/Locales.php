<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

final class Locales
{
    public const array SUPPORTED = ['en', 'ar'];

    public const string DEFAULT = 'en';

    /** Regex for the {locale} route segment. */
    public const string PATTERN = 'en|ar';

    /** Open Graph locale codes. */
    public const array OG = ['en' => 'en_US', 'ar' => 'ar_EG'];

    public static function isSupported(?string $locale): bool
    {
        return in_array($locale, self::SUPPORTED, true);
    }

    public static function dir(?string $locale = null): string
    {
        return ($locale ?? app()->getLocale()) === 'ar' ? 'rtl' : 'ltr';
    }

    public static function other(?string $locale = null): string
    {
        return ($locale ?? app()->getLocale()) === 'ar' ? 'en' : 'ar';
    }

    /** Native name, used by the language switcher. */
    public static function nativeName(string $locale): string
    {
        return $locale === 'ar' ? 'العربية' : 'English';
    }

    /** Best supported locale from Accept-Language, English when nothing matches. */
    public static function fromRequest(Request $request): string
    {
        $preferred = $request->getPreferredLanguage(self::SUPPORTED);

        return self::isSupported($preferred) ? $preferred : self::DEFAULT;
    }

    /** Locale from the first path segment, if it is a supported one. */
    public static function fromPath(Request $request): ?string
    {
        $segment = $request->segment(1);

        return self::isSupported($segment) ? $segment : null;
    }

    /**
     * Same page in another locale, keeping route parameters and query string.
     * Falls back to that locale's home page when there is no named route (e.g. 404s).
     */
    public static function switchUrl(string $locale, ?Request $request = null): string
    {
        $request ??= request();
        $route = $request->route();

        if (! $route instanceof \Illuminate\Routing\Route || ! $route->getName() || ! Route::has($route->getName())) {
            return route('home', ['locale' => $locale]);
        }

        $parameters = array_merge($route->originalParameters(), ['locale' => $locale]);
        $url = route($route->getName(), $parameters);
        $query = $request->getQueryString();

        return $query ? $url.'?'.$query : $url;
    }
}
