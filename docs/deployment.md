# Deployment

The app is host-agnostic: PHP 8.4, MySQL 8, a `database` queue and the Laravel scheduler. No Redis, Node or headless browser is needed on the server (OG images are drawn with GD).

- [1. Server requirements](#1-server-requirements)
- [2. VPS with Ploi or Laravel Forge (recommended)](#2-vps-with-ploi-or-laravel-forge-recommended)
- [3. Shared hosting fallback](#3-shared-hosting-fallback)
- [4. Mail: Resend (default) or Postmark, with SPF / DKIM / DMARC](#4-mail-resend-default-or-postmark-with-spf--dkim--dmarc)
- [5. Production `.env`](#5-production-env)
- [6. After the first deploy](#6-after-the-first-deploy)
- [7. Updating, backups and rollback](#7-updating-backups-and-rollback)

---

## 1. Server requirements

| | |
|---|---|
| PHP | 8.4 with `gd` (compiled with WebP; AVIF optional — the app detects it), `intl`, `exif`, `mbstring`, `pdo_mysql`, `zip`, `bcmath`, `fileinfo`, OPcache on |
| Database | MySQL 8 (or MariaDB 10.6+) with `utf8mb4` |
| Web server | nginx or Caddy (Apache works with the bundled `.htaccess`) with HTTPS, gzip/brotli |
| Build | Node 22 + npm — on the server (VPS) or on your machine (shared hosting) |
| Cron | `* * * * *` scheduler entry |

---

## 2. VPS with Ploi or Laravel Forge (recommended)

1. **Create the server** (Ubuntu 24.04, PHP 8.4, MySQL 8) and a **site** for `omarkhaled.info` with web directory `/public`.
2. **Connect the repository** (private GitHub repo) and branch `main`.
3. **Create a database + user**, then fill the environment (see [§5](#5-production-env)).
4. **Deploy script** (replace the default):

   ```bash
   cd /home/ploi/omarkhaled.info   # Forge: cd /home/forge/omarkhaled.info

   git pull origin main
   composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
   npm ci && npm run build

   php artisan down --retry=15 || true
   php artisan migrate --force
   php artisan storage:link || true
   php artisan optimize          # config, routes, views, events
   php artisan icons:cache
   php artisan filament:optimize
   php artisan responsecache:clear   # REQUIRED: the inlined CSS is CSP-hashed; cached pages must match the new build
   php artisan queue:restart
   php artisan up

   # Ploi/Forge reload PHP-FPM automatically (OPcache).
   ```

   First deploy only, after it succeeds:

   ```bash
   php artisan db:seed --force        # content + settings with placeholders (idempotent, never creates users)
   php artisan portfolio:make-admin   # interactive: email + password; set up MFA at first login
   ```

5. **Queue worker (Supervisor)** — in Ploi: *Queue → Add worker*; in Forge: *Queue → New worker*. Equivalent Supervisor program:

   ```ini
   [program:omarkhaled-worker]
   command=php /home/ploi/omarkhaled.info/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --timeout=300
   autostart=true
   autorestart=true
   stopasgroup=true
   killasgroup=true
   user=ploi
   numprocs=1
   redirect_stderr=true
   stdout_logfile=/home/ploi/omarkhaled.info/storage/logs/worker.log
   stopwaitsecs=360
   ```

   `--timeout=300` leaves room for AVIF conversions of large screenshots.

6. **Scheduler** — Ploi: *Cronjobs → add*; Forge: *Scheduler → New job*:

   ```
   * * * * * cd /home/ploi/omarkhaled.info && php artisan schedule:run >> /dev/null 2>&1
   ```

   It runs the daily inquiry pruning (and, on shared hosting only, the queue — see §3).

7. **HTTPS** — issue a Let's Encrypt certificate, force HTTPS, and redirect `www` → apex (or the reverse; be consistent with `APP_URL`).

8. **nginx additions** (inside the site's `server {}` block; Ploi/Forge let you edit it):

   ```nginx
   # Compression (brotli if the module is installed, gzip always)
   gzip on;
   gzip_types text/plain text/css application/javascript application/json application/xml image/svg+xml;
   gzip_min_length 512;
   # brotli on; brotli_types text/plain text/css application/javascript application/json application/xml image/svg+xml;

   # Hashed build assets and fonts never change: cache for a year
   location ^~ /build/ {
       expires 1y;
       add_header Cache-Control "public, max-age=31536000, immutable";
       access_log off;
       try_files $uri =404;
   }

   # Public media (AVIF/WebP conversions, OG images) — content-addressed or UUID paths
   location ^~ /storage/ {
       expires 30d;
       add_header Cache-Control "public, max-age=2592000";
       try_files $uri =404;
   }

   # Never serve dotfiles
   location ~ /\.(?!well-known) { deny all; }
   ```

   Security headers (CSP, HSTS, etc.) are sent by the application; don't duplicate them in nginx.

9. **Cloudflare (optional)** — proxy the domain, SSL mode *Full (strict)*, leave caching on "Standard": the app sends `Cache-Control: public, max-age=300, s-maxage=600` on public pages and `private` on the contact page and admin. If you enable Turnstile, add its keys to `.env` and switch it on in **Admin → Spam protection**.

---

## 3. Shared hosting fallback

Works on hosts with PHP 8.4, MySQL, SSH or a file manager, and cron (cPanel, Hostinger, etc.).

1. **Build locally** — `composer install --no-dev --optimize-autoloader` and `npm ci && npm run build` on your machine.
2. **Upload** everything except `node_modules/`, `.git/` and `storage/app/seed-media/` (client captures stay private; upload them separately only if you want to import them).
3. **Document root** — point the domain to `public/`. If the host forces `public_html`, put the project one level up and make `public_html` a symlink to `public/` (or copy `public/` into `public_html` and adjust the two paths in `public_html/index.php`).
4. **Environment** — create `.env` from [§5](#5-production-env) and set **`QUEUE_VIA_SCHEDULER=true`** (no Supervisor available).
5. **Run once via SSH** (or the host's "PHP command" tool):

   ```bash
   php artisan key:generate
   php artisan migrate --force
   php artisan db:seed --force
   php artisan storage:link
   php artisan optimize
   php artisan portfolio:make-admin
   ```

6. **Cron** (cPanel → *Cron Jobs*, every minute):

   ```
   * * * * * cd /home/USER/omarkhaled.info && php artisan schedule:run >> /dev/null 2>&1
   ```

   With `QUEUE_VIA_SCHEDULER=true` this also starts `queue:work --stop-when-empty --max-time=50` each minute, so emails and image conversions are processed within about a minute.

7. **Each update** — upload the new build, then run `php artisan migrate --force && php artisan optimize && php artisan responsecache:clear`.

If `storage:link` isn't allowed, create the symlink `public/storage → ../storage/app/public` in the file manager.

---

## 4. Mail: Resend (default) or Postmark, with SPF / DKIM / DMARC

The contact form sends two kinds of email: the inquiry to you (with **Reply-To = the visitor**) and an acknowledgement to the visitor. Both go **from your own domain** — never from the visitor's address — so they need proper authentication or they'll land in spam.

### Option A — Resend (default, already installed)

1. Create a Resend account → **Domains → Add domain** → `omarkhaled.info` (choose the region closest to you, e.g. `eu-west-1`).
2. Add the DNS records Resend shows. They look like this — **copy the exact values from the dashboard**:

   | Type | Name | Value | Purpose |
   |---|---|---|---|
   | TXT | `resend._domainkey` | `p=MIGfMA0GCSq…` (long public key) | **DKIM** |
   | MX | `send` | `feedback-smtp.<region>.amazonses.com` (priority 10) | bounce handling |
   | TXT | `send` | `v=spf1 include:amazonses.com ~all` | **SPF** for the sending subdomain |

   Your root SPF record (for mailboxes like Google Workspace) is untouched because Resend sends via the `send.` subdomain.
3. Wait for **Verified**, then create an API key with **Sending access** only for this domain.
4. `.env`:

   ```dotenv
   MAIL_MAILER=resend
   RESEND_API_KEY=            # paste the key on the server only
   MAIL_FROM_ADDRESS="hello@omarkhaled.info"
   MAIL_FROM_NAME="Omar Khaled"
   ```

### Option B — Postmark

1. Install the transport: `composer require symfony/postmark-mailer symfony/http-client`.
2. Postmark → **Sender signatures → Add domain** → `omarkhaled.info`, then add:

   | Type | Name | Value | Purpose |
   |---|---|---|---|
   | TXT | `<date>pm._domainkey` (e.g. `20261007pm._domainkey`) | `k=rsa; p=MIGfMA0…` | **DKIM** |
   | CNAME | `pm-bounces` | `pm.mtasv.net` | custom Return-Path (aligns **SPF**) |

3. Create a **Server** (transactional stream) and copy its **Server API token**.
4. `.env`:

   ```dotenv
   MAIL_MAILER=postmark
   POSTMARK_API_KEY=          # the Server API token, on the server only
   MAIL_FROM_ADDRESS="hello@omarkhaled.info"
   ```

### DMARC (both options)

Add a DMARC policy once SPF/DKIM verify. Start in monitoring mode, tighten after a few weeks of clean reports:

| Type | Name | Value |
|---|---|---|
| TXT | `_dmarc` | `v=DMARC1; p=none; rua=mailto:dmarc-reports@omarkhaled.info; adkim=r; aspf=r` |

Then move to `p=quarantine` (and eventually `p=reject`) when reports show only your legitimate senders. Free report viewers: Postmark's DMARC digests, dmarcian, Cloudflare DMARC Management.

### Check deliverability

- Send a test inquiry from the live site and check **"Show original"** in Gmail: `SPF: PASS`, `DKIM: PASS`, `DMARC: PASS`.
- <https://www.mail-tester.com> should score 9–10/10.
- Set the real recipient in **Admin → Contact & social → Contact email** (until then `MAIL_CONTACT_ADDRESS` is used).

---

## 5. Production `.env`

```dotenv
APP_NAME="Omar Khaled"
APP_ENV=production
APP_KEY=                          # php artisan key:generate (keep it secret; rotate if ever leaked)
APP_DEBUG=false
APP_URL=https://omarkhaled.info

LOG_CHANNEL=daily
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=omarkhaled
DB_USERNAME=omarkhaled
DB_PASSWORD=                      # server only

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=database
QUEUE_VIA_SCHEDULER=false         # true on shared hosting

SETTINGS_CACHE_ENABLED=true
RESPONSE_CACHE_ENABLED=true
RESPONSE_CACHE_LIFETIME=604800

MAIL_MAILER=resend
RESEND_API_KEY=                   # server only
MAIL_FROM_ADDRESS="hello@omarkhaled.info"
MAIL_FROM_NAME="${APP_NAME}"
MAIL_CONTACT_ADDRESS=             # where inquiries go while the settings email is a placeholder

ADMIN_PATH=                       # something non-obvious, e.g. "studio-" + 4 random characters
TURNSTILE_SITE_KEY=
TURNSTILE_SECRET_KEY=
```

Never commit `.env`. Keep `APP_KEY` stable across deploys (changing it logs everyone out and invalidates encrypted MFA secrets — use `APP_PREVIOUS_KEYS` when rotating).

---

## 6. After the first deploy

1. `php artisan portfolio:make-admin`, sign in at `https://omarkhaled.info/{ADMIN_PATH}`, scan the QR code with an authenticator app and **store the recovery codes**.
2. Work through the **dashboard launch checklist**: real contact email, Upwork URL, WhatsApp (or leave empty), years of experience, testimonials, experience dates, project results.
3. **Admin → SEO**: indexing **on** (production), paste the Google Search Console and Bing verification codes.
4. Visit `/robots.txt`, `/sitemap.xml`, `/llms.txt` and one page in each language; check the response headers include `Content-Security-Policy` and `Strict-Transport-Security`.
5. Send yourself a test inquiry (see §4).
6. Continue with [docs/seo-checklist.md](seo-checklist.md).

**Staging:** use a separate `.env` with **Admin → SEO → indexing off** — every page becomes `noindex` and `robots.txt` disallows everything.

---

## 7. Updating, backups and rollback

- **Updates:** push to `main` → the deploy script runs. CI (`.github/workflows/ci.yml`) runs Pint, Larastan, Pest, `composer audit` and `npm audit` on every push.
- **Backups:** daily MySQL dumps (Ploi/Forge backups → S3-compatible storage) **plus** `storage/app/public` and `storage/app/private-media` (uploaded media). Keep 14+ days.
- **Rollback:** redeploy the previous commit (`git checkout <sha>` + deploy script). Migrations are additive; if a migration must be reverted, `php artisan migrate:rollback --step=1` before checking out the old commit.
- **Dependency updates:** `composer update` / `npm update` on a branch, `composer test`, deploy.
