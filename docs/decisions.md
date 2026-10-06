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
