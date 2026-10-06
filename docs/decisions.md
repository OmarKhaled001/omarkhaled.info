# Decision log

Decisions made autonomously during the build. Each entry: **decision**, **why**, **how to change it later**.

---

### D-001 · Local database is SQLite; production is MySQL 8
- **Why:** zero-setup local dev and fast in-memory tests; all queries stay portable (no MySQL-only SQL).
- **Change:** set `DB_CONNECTION=mysql` and the `DB_*` values in `.env`, then `php artisan migrate --force`.

### D-002 · Pest 4 (not 5)
- **Why:** Pest 4 resolved cleanly with Laravel 13 / PHPUnit 12 and is what the spec names.
- **Change:** `composer require --dev pestphp/pest:^5 pestphp/pest-plugin-laravel:^5 -W`.

### D-003 · Resend driver installed by default, Postmark documented
- **Why:** Resend is the chosen default; Laravel's `resend` transport needs `resend/resend-php`, which is installed. Postmark needs two extra packages, so it is documented rather than installed.
- **Change:** see `docs/deployment.md` → Mail.

### D-004 · Larastan level 6 on `app/`
- **Why:** catches real type bugs without forcing generics boilerplate everywhere.
- **Change:** `phpstan.neon` → `level`.

### D-005 · Local preview server runs via `php artisan serve` in the background, not the preview launcher
- **Why:** on 2026-10-06 the preview launcher resolved `/home/mora-khaled/projects/.claude/launch.json` instead of this repo's and briefly started the **client-manager** dev server (`npm run dev`, port 3443). It was stopped within seconds; no files in `client-manager/` changed (verified with `find -newermt`). To guarantee no client app is ever started again, the portfolio is served with `php artisan serve --port=8000` from this repo only and the browser is pointed at that URL.
- **Change:** use `.claude/launch.json` (config `portfolio`) once the launcher resolves this repo.

### D-006 · Placeholders are hidden from visible pages too, not only from JSON-LD/sitemap/llms.txt
- **Why:** shipping `contact@example.com` or a dead Upwork link on a live page looks broken to a prospect. All public reads go through `App\Support\Profile`, which returns `null` for placeholders, so the field simply doesn't render.
- **Change:** edit `config/portfolio.php` → `placeholders` (what counts as a placeholder), or fill the real values in Admin → Settings.

### D-007 · Accent colour: one base colour in Site Settings, all other accent tokens derived
- **Why:** you asked for the accent to be editable. Deriving text/fill/on-accent/focus variants from one base (`AccentPalette`) guarantees WCAG AA in both themes whatever colour is picked (unit-tested against 8 very different colours). The tokens are emitted as a small inline `<style>` whose SHA-256 is allowed by the CSP.
- **Change:** Admin → Settings → Design → Accent colour.

### D-008 · Technologies get a `show_in_stack` flag
- **Why:** the home page "trusted stack" row should show the headline tools (Laravel, Filament, Livewire…) — not all 40 technologies attached to projects.
- **Change:** toggle per technology in Admin → Technologies.

### D-009 · Settings cache is off by default
- **Why:** avoids stale settings during local work. Production should set `SETTINGS_CACHE_ENABLED=true` (see docs/deployment.md); saving settings in Filament clears it.
- **Change:** `.env` → `SETTINGS_CACHE_ENABLED`.

### D-010 · Admin: in-house side-by-side EN | AR fields instead of a translatable plugin
- **Why:** spatie/laravel-translatable already serialises every locale in `attributesToArray()`, so Filament fills and saves `title.en` / `title.ar` natively — no plugin (and no dependency on a plugin's Filament 5 support). Side-by-side fields also make writing both languages easier.
- **Change:** `app/Filament/Support/Bilingual.php`.

### D-011 · Admin MFA is mandatory (TOTP authenticator app, with recovery codes)
- **Why:** the panel controls what client information is public and receives inquiries; a password alone isn't enough. Admins are created only via `php artisan portfolio:make-admin` (password typed, never stored in code/seeders).
- **Change:** `AdminPanelProvider` → `multiFactorAuthentication(..., isRequired: false)`.

### D-012 · Admin panel fonts are self-hosted too
- **Why:** Filament defaults to Bunny Fonts (a third-party request + extra CSP origin). A tiny Vite-built `admin-fonts.css` reuses the site's Geist/Plex files.
- **Change:** `AdminPanelProvider::font()`.

### D-013 · Private media can be previewed in the admin through signed, expiring URLs only
- **Why:** Filament's upload field needs a preview URL. `media_private` uses Laravel's local-disk `serve` option, which only serves **temporary signed** URLs; there is still no permanent public URL for hidden screenshots or logos.
- **Change:** `config/filesystems.php` → `media_private.serve`.

### D-014 · Inquiry CSV export is a bulk action with CSV-injection protection
- **Why:** Filament's exporter needs extra tables and a queue worker for a handful of rows. Cells starting with `= + - @` are prefixed so spreadsheets never execute them.
- **Change:** `ContactSubmissionsTable::csv()`.

### D-015 · Client Control Panel and ArchPrep are seeded as "own product" (engagement: solo)
- **Why:** both are your own tools/products, not client commissions. Every other project uses the default "client". All of them still start anonymized.
- **Change:** Admin → Projects → Identity → Engagement type.

### D-016 · Screenshots: only viewport captures are imported; ArchPrep has no screenshots
- **Why:** full-page captures of scroll-pinned (GSAP) sections render as empty bands. Step 12 (running client apps for admin screenshots) was skipped as instructed, so ArchPrep and the other projects without public pages use the generated plate visuals. 32 captures (Petrogina, Cairo Key, Printalia) are stored privately and stay hidden until `show_screenshots` is turned on.
- **Change:** upload images in Admin → Projects → Media, or `php artisan portfolio:import-media <folder>`.

### D-017 · Experience entries and testimonials are seeded but hidden
- **Why:** dates and roles from the old portfolio draft are unconfirmed, and no real testimonials exist. Both sections render only when real, published entries exist; the launch checklist reminds you.
- **Change:** Admin → Experience / Testimonials.

### D-018 · Seeders are idempotent and never touch users or Site Settings
- **Why:** re-running `php artisan db:seed` in production refreshes copy without wiping admin edits to settings or creating credentials. Note: it does overwrite project/service copy with the seed text — after launch, edit content in the admin instead of re-seeding.
- **Change:** `database/seeders/*`.

### D-019 · Anonymized projects get generated SVG "plates" instead of images
- **Why:** anonymized work can't show screenshots, and blank cards look unfinished. Each plate is drawn server-side from the project's index, category and stack (a print-plate motif with registration marks) — zero image bytes, crisp in both themes, impossible to identify a client from.
- **Change:** upload a non-identifying cover in Admin → Projects → Media, or reveal screenshots.

### D-020 · Project filters are plain links; filtered views are `noindex, follow`
- **Why:** server-rendered links work without JavaScript, are crawlable, and cache per URL. Filtered pages duplicate the index, so they point their canonical to `/projects` and stay out of search results.

### D-021 · Contact anti-spam: bots get a fake success; rate limits are checked before validation
- **Why:** honeypot and time-trap hits are stored as `spam` (reviewable in the inbox) and shown the normal success screen, so bots learn nothing. Rate limits (3/10 min and 10/day per IP, 3/day per email) run before validation so the form can't be probed for free. IPs are stored only as an HMAC hash.
- **Change:** `config/portfolio.php` → `contact`.

### D-022 · The auto-reply echoes nothing but the sender's first name, at most once per address per day
- **Why:** an auto-reply that repeats user input turns a contact form into a spam relay to any address. Sent in the language of the page the visitor used, RTL for Arabic.

### D-023 · Turnstile fails closed
- **Why:** if Cloudflare can't be reached the submission is refused with a clear message (email is shown as an alternative) rather than letting unverified traffic through while the check is enabled.

### D-024 · `email:rfc,dns` validation only in production
- **Why:** DNS lookups make tests slow and flaky offline; production still rejects domains without mail servers.

### D-025 · Shared-hosting queue fallback via the scheduler
- **Why:** shared hosts rarely allow Supervisor. With `QUEUE_VIA_SCHEDULER=true`, the per-minute cron runs `queue:work --stop-when-empty --max-time=50`. VPS deployments keep it `false` and use Supervisor.

### D-026 · OG images are generated on first request and content-addressed (not by a queued job on save)
- **Why:** the file name is a hash of everything drawn (title, eyebrow, locale, accent), so a card can never be stale — editing a title or revealing a client simply produces a new file, and anonymized projects always get anonymized cards. It renders with GD + ar-php (Arabic shaping), so no headless Chrome is needed on the server. With full-page caching, generation happens once per card.
- **Change:** `App\Support\Seo\OgImage` (bump `VERSION` to regenerate all cards after a design change).

### D-027 · JSON-LD and the sitemap are built by hand; spatie/schema-org and spatie/laravel-sitemap were removed
- **Why:** plain arrays are easier to test and keep the graph in one place; the sitemap needs `x-default` alternates, which is a 15-line Blade template. Fewer dependencies to upgrade.

### D-028 · FAQPage schema is emitted even though Google limits FAQ rich results
- **Why:** since 2023 Google shows FAQ rich results only for authoritative sites, but the markup still helps AI answer engines and costs nothing.

### D-029 · robots.txt lists AI crawlers explicitly and never mentions the admin path
- **Why:** explicit `Allow` groups for GPTBot, ClaudeBot, PerplexityBot, Google-Extended, etc. make the GEO intent unambiguous. Listing the admin path would advertise it; the panel sends `X-Robots-Tag: noindex` instead.

### D-030 · llms.txt is English-only and served as text/plain
- **Why:** AI crawlers consume one canonical language best; the Arabic site is linked from the "Optional" section. `text/plain; charset=utf-8` displays correctly in every browser and crawler (Markdown inside).

### D-031 · Third-person copy avoids gendered pronouns
- **Why:** pronouns weren't specified, so third-person copy (FAQ, llms.txt, privacy) uses the name or neutral phrasing instead of guessing. First-person copy ("I'm Omar…") is unaffected.
- **Change:** edit the copy in the admin if you'd like pronouns used.

### D-032 · CSP per surface (public / contact / admin), hashes instead of nonces on public pages
- **Why:** nonces don't work with full-page caching (a cached nonce never matches a new header). Public pages allow exactly two inline blocks by SHA-256 (the pre-paint theme script and the accent tokens), no `unsafe-inline`/`unsafe-eval`. The contact page needs `unsafe-eval` for Alpine (Livewire 4 is not in CSP-safe mode because Filament's Alpine expressions require eval); the admin needs inline code and sits behind login + MFA. Scroll-reveal delays moved from inline `style` attributes to `data-reveal-delay` so nothing inline violates the policy.
- **Change:** `App\Http\Middleware\SecurityHeaders`.

### D-033 · Full-page cache: only 200 responses on session-less routes; any content/settings change clears everything
- **Why:** the `/` redirect varies by Accept-Language and 404s must not stick. The site is small, so clearing the whole cache on any save is simpler and always correct. Browsers/CDNs get `max-age=300, s-maxage=600, stale-while-revalidate=86400`.
- **Change:** `App\Http\Cache\PublicPageCacheProfile`, `AppServiceProvider::CACHED_MODELS`, `RESPONSE_CACHE_*` env.

### D-034 · Filament avatars are local SVG initials
- **Why:** Filament's default avatar provider calls ui-avatars.com, sending the admin's name to a third party and needing an extra CSP origin.

### D-035 · The built stylesheet is inlined into every page
- **Why:** it is ~6 KB compressed and was the only render-blocking request; inlining moved English LCP from 1.8 s to 1.5–1.65 s. The CSP allows it by SHA-256. **Deploys must run `php artisan responsecache:clear` after `npm run build`** (the deploy script in docs/deployment.md does).
- **Change:** `App\Support\Design\InlineCss` (return null to go back to a `<link>`).

### D-036 · Font loading tuned with measurements
- **Why:** see docs/lighthouse.md. Arabic fonts are subset to the Arabic blocks (presentation forms aren't needed for browser shaping), Plex 500 was dropped (500 falls back to 400), Geist Mono is `optional` (small labels never shift layout).
