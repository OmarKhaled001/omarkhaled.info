<?php

namespace App\Support\Spam;

use App\Settings\SpamSettings;
use Illuminate\Support\Facades\Http;
use Throwable;

/** Optional Cloudflare Turnstile check. Keys come from .env; the admin toggle switches it on. */
final class Turnstile
{
    public const string VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function enabled(): bool
    {
        try {
            $on = app(SpamSettings::class)->turnstile_enabled;
        } catch (Throwable) {
            $on = false;
        }

        return $on && filled($this->siteKey()) && filled(config('portfolio.turnstile.secret_key'));
    }

    public function siteKey(): ?string
    {
        $key = config('portfolio.turnstile.site_key');

        return is_string($key) && $key !== '' ? $key : null;
    }

    public function verify(?string $token, ?string $ip): bool
    {
        if (! $this->enabled()) {
            return true;
        }

        if (blank($token)) {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(5)->post(self::VERIFY_URL, [
                'secret' => config('portfolio.turnstile.secret_key'),
                'response' => $token,
                'remoteip' => $ip,
            ]);

            return $response->ok() && $response->json('success') === true;
        } catch (Throwable) {
            // Fail closed: if Cloudflare cannot be reached the visitor can retry or email directly.
            return false;
        }
    }
}
