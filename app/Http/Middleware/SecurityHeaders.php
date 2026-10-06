<?php

namespace App\Http\Middleware;

use App\Support\Design\Theme;
use App\Support\Spam\Turnstile;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Security headers for every response, with a CSP per surface:
 * - public pages (full-page cached): no inline code except two hashed blocks (theme script, accent CSS);
 * - contact page / Livewire: Alpine needs 'unsafe-eval'; optional Turnstile origins;
 * - Filament admin (behind login + MFA): Filament needs inline scripts/styles.
 */
class SecurityHeaders
{
    private const string TURNSTILE = 'https://challenges.cloudflare.com';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $headers = $response->headers;

        $headers->set('Content-Security-Policy', $this->policy($request));
        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('X-Frame-Options', 'DENY');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()');
        $headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $headers->remove('X-Powered-By');

        if (app()->isProduction() && $request->isSecure()) {
            $headers->set('Strict-Transport-Security', 'max-age=63072000; includeSubDomains; preload');
        }

        return $response;
    }

    private function policy(Request $request): string
    {
        $admin = trim((string) config('portfolio.admin_path', 'admin'), '/');
        $isAdmin = $request->is($admin, "{$admin}/*", 'filament/*');
        $isInteractive = $request->routeIs('contact') || $request->is('livewire/*', 'livewire-*/*');

        $script = ["'self'"];
        $style = ["'self'"];
        $connect = ["'self'"];
        $frame = ["'none'"];

        if ($isAdmin) {
            array_push($script, "'unsafe-inline'", "'unsafe-eval'");
            $style[] = "'unsafe-inline'";
        } elseif ($isInteractive) {
            array_push($script, Theme::scriptHash(), "'unsafe-eval'");
            $style[] = "'unsafe-inline'";

            if ($this->turnstileEnabled()) {
                $script[] = self::TURNSTILE;
                $connect[] = self::TURNSTILE;
                $frame = [self::TURNSTILE];
            }
        } else {
            $script[] = Theme::scriptHash();
            $style[] = $this->accentHash();
        }

        if (Vite::isRunningHot()) {
            $hot = rtrim((string) file_get_contents(public_path('hot')));
            $script[] = $hot;
            $style[] = $hot;
            $connect[] = $hot;
            $connect[] = preg_replace('#^http#', 'ws', $hot);
        }

        $directives = [
            "default-src 'self'",
            'script-src '.implode(' ', $script),
            'style-src '.implode(' ', $style),
            "img-src 'self' data: blob:",
            "font-src 'self'",
            'connect-src '.implode(' ', $connect),
            'frame-src '.implode(' ', $frame),
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ];

        if (app()->isProduction()) {
            $directives[] = 'upgrade-insecure-requests';
        }

        return implode('; ', $directives);
    }

    private function accentHash(): string
    {
        try {
            return Theme::styleHash();
        } catch (Throwable) {
            return "'self'";
        }
    }

    private function turnstileEnabled(): bool
    {
        try {
            return app(Turnstile::class)->enabled();
        } catch (Throwable) {
            return false;
        }
    }
}
