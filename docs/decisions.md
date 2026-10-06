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
