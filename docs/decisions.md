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

### D-037 · Hero: short headline + WebGL "platform stack" driven by scroll
- **Why:** the request was a simpler title, 3D and scroll animation. The model (database → Laravel core → Filament admin → interface) explains what Omar builds; on tall desktop screens the hero pins and scrolling pulls the layers apart, with labels attached to the projected plates. Raw WebGL2 instead of three.js keeps it at ~5 KB gzipped; it loads after `load` + idle so the H1 stays the LCP element, renders only while something moves, and follows the theme/accent tokens.
- **Software rendering:** headless Chrome (Lighthouse/PageSpeed), VMs and blocklisted GPUs rasterise WebGL in software and read every frame back on the main thread (~115 ms per frame under mobile throttling, TBT 15 s in the first measurement). Those renderers get a CSS-3D version of the stack instead, so audits measure TBT ≈ 0 while GPU devices get WebGL. Reduced motion: static exploded model, no pinning.
- **Change:** `resources/js/hero-scene.js`, `pin` custom variant + `.hero-*` styles in `app.css`, `Profile::heroHeadlineHtml()`, settings migration `2026_10_07_120000_simplify_hero_copy` (only replaces untouched defaults).

### D-038 · The site addresses clients and employers; CV is managed in the admin
- **Why:** Omar is open to client projects and to full-time/contract roles. Copy, meta, the hero, a "Two ways to work together" section, the CTA band and the contact form ("Job or contract role", no budget asked) speak to both. `CareerSettings` holds the *open to roles* switch, the hiring note and the CV files (public disk, deleted when replaced); `/{locale}/cv` serves them with a readable file name and `noindex`. Nothing CV-related renders until a CV is uploaded, so there is never a dead button.
- **Change:** `App\Settings\CareerSettings`, `ManageCareer`, `CvController`, `ContactForm::isRoleInquiry()`, settings migration `2026_10_07_150000_add_career_and_brand_settings`.

### D-039 · Logo is an upload, favicons are generated from it
- **Why:** the new OK monogram should replace the built-in mark without a deploy, and future changes should not need one either. Logos are raster uploads only (no SVG: an uploaded SVG served from the site's origin could carry script). Without a dark variant, the logo uses `mix-blend-mode` so a white background disappears and `invert + hue-rotate(180°)` in dark mode (black becomes white, gold stays gold-ish). Saving the Design settings prunes old files and regenerates 32/192 px favicons and the 180 px touch icon with GD.
- **Change:** `App\Support\Design\Brand`, `ManageDesign`, `<x-wordmark>`, layout favicons, Filament `brandLogo()`.

### D-040 · About page word portrait (photo drawn with moving words)
- **Why:** requested in the style of a typographic portrait. Only a 180 px grayscale light map, contrast-stretched and cropped to the lit face at 3:4, is published (`storage/portrait/map-*.png`); the original photo stays on the private `media_private` disk. The browser packs words from the site's own vocabulary (EN/AR lists in `lang/*/about.php`) into the light areas, brightest first, and uses the map as an alpha mask so features read pixel by pixel.
- **Motion:** assembles on first view (layout time-boxed to 6 ms per frame), words keep swapping with a brief flash, a light band sweeps every ~6 s, the pointer is a spotlight, the title lines drift with scroll. The module (~3 KB gz) loads only when the section nears the viewport, pauses off screen, and reduced motion gets the finished still.
- **Change:** `App\Support\Design\Portrait`, `resources/js/word-portrait.js`, Identity settings (portrait upload), settings migration `2026_10_07_180000_add_portrait_settings`.

### D-041 · Uploaded logos are optimised on save
- **Why:** the first real logo upload was a 617 KB PNG, uploaded twice (light and dark): 1.2 MB in the header cost ~6 s of mobile LCP (Lighthouse 75). Saving Design settings now trims the margin (transparent or white) and writes a 112 px-high WebP (~7 KB) that the site uses instead; a dark logo identical to the light one is ignored so the automatic dark-mode recolouring applies. Back to 99–100.
- **Change:** `Brand::regenerateIcons()`, `Brand::logo()`.

### D-042 · About portrait is a real photo (supersedes the word portrait of D-040)
- **Why:** the owner preferred the photo itself over the word effect. The section now follows the site theme (title, role, intro, CTAs) beside the photo in a card; the upload stays private and saving Identity settings publishes AVIF + WebP at 480/800/1200 px (re-encoding drops EXIF/GPS), served with `<picture>`, `srcset`, `loading="lazy"` and fixed dimensions (no CLS). The largest WebP is the `Person.image` in JSON-LD. `word-portrait.js` and the light map were removed.
- **Note:** the variants are stored as a JSON string setting — spatie/laravel-settings resolves property docblocks and cannot parse array shapes (it took the settings class down during development).
- **Change:** `App\Support\Design\Portrait`, `pages/about.blade.php`, `SchemaGraph::person()`, settings migration `2026_10_07_200000_portrait_photo_instead_of_word_map`.

### D-043 · Feathered portrait edges
- **Why:** requested: the photo should melt into the page rather than sit in a card. A CSS mask intersects a soft ellipse with vertical and horizontal ramps that reach zero exactly at the image box, deepest at the bottom where the shoulders are cut. Lossy WebP/AVIF encode the transparent background with alpha 1–6/255; the ramps make sure that noise never forms a visible frame (measured: edge pixels equal the page colour in both themes).
- **Change:** `.photo-feather` in `app.css`, About photo markup.

### D-044 · Owner-approved project showcase with structured sections
- **Why:** the owner supplied a reviewed spreadsheet (projects-portfolio.xlsx) and device mockups for five projects and asked for them to be shown with their details in both languages. The mockups show each live domain, so those projects are revealed (client name, live link); only Printalia's repository is linked because it is the only public one (checked with `gh repo view`). The baseline in `projects.php` stays anonymized; `project-showcase.php` layers the approved copy on top, so reverting a project is one deleted block.
- **New sections:** `goals`, `audience` and `journey` (translatable HTML columns) render between challenge, solution and architecture — `Project::SECTIONS` is the single list used by the presenter, the case-study view, the leak guard, llms.txt and the admin form.
- **Honesty:** the spreadsheet's verification limits were applied — e.g. CairoKey no longer claims Google sign-in or self-service booking, NARRVA's Paymob is described as an integration in the code, Petrogina's tracking as recorded information with carrier links, Printalia's payments as receipt + admin approval. Printalia's facts (3 panels, 11 modules, 14 models) were read from its public repository.
- **Covers:** committed under `database/seeders/media/covers` and attached to the public `cover` collection by the seeder (idempotent). Disabled in tests via `SEED_COVER_MOCKUPS=false` — AVIF conversions on the sync queue made the suite run for >10 minutes.

### D-045 · Cairo for Arabic; layout gaps closed; per-word RTL layout in OG cards
- **Cairo** replaces IBM Plex Sans Arabic on the site, in the admin panel and in OG cards (owner's request). One variable Arabic-subset file (31 KB, weights 200–1000, from @fontsource-variable/cairo) replaces two static Plex files (68 KB) and is the only Arabic preload; Arabic LCP went from 1.8–1.9 s to 1.6–1.7 s with CLS 0. The OG TTF merges Cairo's Arabic and Latin SemiBold subsets with fontTools so mixed titles render in one face.
- **Arabic labels:** `.slug` / `.font-mono` letter-spacing broke Arabic joining; an unlayered `:lang(ar)` rule (it must beat Tailwind's utilities layer) switches them to Cairo with no tracking.
- **Gaps:** the featured bento card's text no longer stretches (the cover absorbs the tall cell), covers are centred instead of top-anchored, and the case-study facts are a wrapping flex row so 4–7 items never leave empty cells.
- **OG cards:** ar-php's bidi moved spaces around Latin runs ("مطوّرLaravel"). Lines are now assembled per word in visual order: Latin runs kept left-to-right, Arabic words shaped individually, mixed words like "وFilament" split by script.

### D-046 · Deployable on hosts without proc_open
- **Why:** the Hostinger Git deploy failed twice: first on PHP 8.3 (the lock needs ≥ 8.4.1 — now declared as `php: ^8.4` with `ext-gd`/`ext-intl`), then because `proc_open` is disabled, so Composer could not run the `@php artisan …` post-autoload-dump scripts. The same restriction would have broken `Schedule::command()` and media-library's external image optimizers at runtime.
- **Change:** post-autoload-dump keeps only the pure-PHP Laravel callback (the package manifest rebuilds itself on first request); Artisan scripts moved to post-update-cmd; scheduler tasks use `Schedule::call` + `Artisan::call`; conversions are `nonOptimized()` (GD already encodes at the set quality); `public/build` and Filament's published assets are committed because the deploy runs no npm/Artisan step.
