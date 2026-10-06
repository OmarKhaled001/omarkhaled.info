<?php

namespace App\Http\Cache;

use Illuminate\Http\Request;
use Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests;
use Symfony\Component\HttpFoundation\Response;

/**
 * Full-page cache for the public, session-less pages. Only 200 responses are cached:
 * the "/" redirect depends on Accept-Language and 404s must not be pinned.
 */
class PublicPageCacheProfile extends CacheAllSuccessfulGetRequests
{
    public function hasCacheableResponseCode(Response $response): bool
    {
        return $response->getStatusCode() === 200;
    }

    public function hasCacheableContentType(Response $response): bool
    {
        $type = (string) $response->headers->get('Content-Type', '');

        return parent::hasCacheableContentType($response) || str_starts_with($type, 'application/xml');
    }

    public function useCacheNameSuffix(Request $request): string
    {
        // Public pages never depend on the visitor.
        return '';
    }
}
