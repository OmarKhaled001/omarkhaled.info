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
