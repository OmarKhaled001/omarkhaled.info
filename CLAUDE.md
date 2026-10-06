# omarkhaled.info — agent notes

Bilingual (en/ar) portfolio for Omar Khaled. Laravel 13, Filament 5, Livewire 4, Tailwind v4, Pest 4. Spec: `docs/spec.md`. Decisions: `docs/decisions.md`.

## Non-negotiables
- **Anonymity:** public output about a project goes through `App\Presenters\PublicProject`. Never echo `client_name`, `live_url`, `repo_url`, logos or screenshots from the model directly. `AnonymityGuard` tests fail on leaks.
- **Placeholders:** public output reads identity/contact values through `App\Support\Profile`, which drops placeholders. Never read `ContactSettings` directly in public views, JSON-LD, sitemap or llms.txt.
- **No client files** from `/home/mora-khaled/projects/*` are ever copied into this repo. Client screenshots live only in gitignored `storage/app/seed-media/` and private media storage.
- **No secrets** in any committed file; `.env.example` holds empty values only (a test enforces this).
- **RTL:** logical Tailwind utilities only (`ms-/me-/ps-/pe-/start-/end-/text-start`). A test greps views for physical ones.
- **Public pages** use the `public` middleware group: no session, no cookies, cacheable. Only `/contact` uses `web`.

## Commands
- `composer test` — Pint check, Larastan, Pest
- `npm run build` — assets
- `php artisan portfolio:make-admin` — create an admin user
