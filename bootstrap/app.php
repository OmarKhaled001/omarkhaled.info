<?php

use App\Http\Middleware\SetLocale;
use App\Support\Locales;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            // Public marketing pages: no session, no cookies, no CSRF -> fully cacheable.
            Route::middleware('public')->group(base_path('routes/site.php'));
            // Locale-less public endpoints: "/", sitemap, robots, llms.
            Route::middleware('public.static')->group(base_path('routes/static.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->group('public', [
            SetLocale::class,
            SubstituteBindings::class,
        ]);

        $middleware->group('public.static', [
            SubstituteBindings::class,
        ]);

        $middleware->alias([
            'locale' => SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Unmatched URLs never pass through SetLocale, so infer the locale for the branded 404 page.
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            $locale = Locales::fromPath($request) ?? Locales::fromRequest($request);
            app()->setLocale($locale);
            URL::defaults(['locale' => $locale]);

            return response()->view('errors.404', [], 404);
        });
    })->create();
