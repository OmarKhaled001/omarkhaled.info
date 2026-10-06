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
