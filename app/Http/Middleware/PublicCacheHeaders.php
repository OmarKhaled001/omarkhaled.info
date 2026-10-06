<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Lets browsers and CDNs reuse public pages briefly while the server-side cache does the heavy lifting. */
class PublicCacheHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethodCacheable() && $response->getStatusCode() === 200) {
            $response->headers->set('Cache-Control', 'public, max-age=300, s-maxage=600, stale-while-revalidate=86400');
        } elseif ($response->isRedirection()) {
            $response->headers->set('Cache-Control', 'private, no-cache');
        }

        return $response;
    }
}
