# omarkhaled.info

Bilingual (English / Arabic) portfolio and lead-generation site for **Omar Khaled**, full-stack Laravel & Filament developer.

- **Stack:** Laravel 13 · PHP 8.4 · Filament 5 · Livewire 4 · Tailwind CSS 4 · Vite · Pest 4 · MySQL 8 (SQLite locally)
- **Public site:** server-rendered Blade, ~0.7 KB of JavaScript, full-page cached, `/en/...` and `/ar/...` with RTL
- **Admin:** Filament panel with mandatory TOTP MFA — projects, services, FAQs, testimonials, pages, inquiries, Site Settings, launch checklist
- **SEO/GEO:** per-page meta + hreflang, JSON-LD graph, generated OG images, sitemap, AI-friendly robots.txt, `llms.txt` / `llms-full.txt`
- **Quality:** Lighthouse mobile 99–100 in all categories, axe WCAG 2.2 AA clean, 150+ Pest tests including a rendered-page client-leak check

Docs: [spec](docs/spec.md) · [decisions](docs/decisions.md) · [blockers](docs/blockers.md) · [deployment](docs/deployment.md) · [SEO checklist](docs/seo-checklist.md) · [Lighthouse & QA](docs/lighthouse.md) · [projects inventory](docs/projects-inventory.md)

---

## Local setup

Requirements: PHP 8.4 with `gd` (WebP; AVIF optional), `intl`, `exif`, `pdo_sqlite`, `zip`, `bcmath` · Composer 2 · Node 22.

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed          # content, settings (with placeholders), private screenshots if present
php artisan storage:link
npm run build                        # or: npm run dev
php artisan portfolio:make-admin     # prompts for email + password; set up your authenticator app on first login
php artisan serve
```

Open <http://127.0.0.1:8000> (redirects to `/en` or `/ar`) and the admin at <http://127.0.0.1:8000/admin>.

Queued work (image conversions, emails) runs on the `database` queue: `php artisan queue:work` in a second terminal.
Locally `MAIL_MAILER=log`, so emails are written to `storage/logs/laravel.log` and never sent.

### Commands

| Command | What it does |
|---|---|
| `composer test` | Pint (check), Larastan level 6, Pest |
| `php artisan portfolio:make-admin` | Create or promote the admin user (password is typed, never stored in code) |
| `php artisan portfolio:import-media [path]` | Import captured screenshots (folder with `manifest.json`) into **private** project media |
| `php artisan responsecache:clear` | Clear the full-page cache (also runs automatically when content or settings change) |
| `php artisan model:prune` | Apply the inquiry retention policy (scheduled daily) |

---

## Environment variables

Everything not listed keeps Laravel's defaults. `.env.example` never contains secret values (a test enforces it).

| Variable | Purpose | Example |
|---|---|---|
| `APP_URL` | Canonical origin used in URLs, sitemap, JSON-LD | `https://omarkhaled.info` |
| `APP_ENV` / `APP_DEBUG` | Production: `production` / `false` | |
| `DB_CONNECTION` + `DB_*` | `sqlite` locally, `mysql` in production | |
| `QUEUE_CONNECTION` | `database` (works everywhere) | |
| `QUEUE_VIA_SCHEDULER` | `true` on shared hosting without Supervisor | `false` |
| `MAIL_MAILER` | `log` locally; `resend` (default) or `postmark` in production | `resend` |
| `RESEND_API_KEY` | Resend API key (production) | — |
| `POSTMARK_API_KEY` | Only if you use Postmark instead | — |
| `MAIL_FROM_ADDRESS` / `MAIL_FROM_NAME` | Sender on your own domain (SPF/DKIM/DMARC: see deployment) | `hello@omarkhaled.info` |
| `MAIL_CONTACT_ADDRESS` | Fallback inquiry recipient while the Site Settings email is still a placeholder | `you@yourmail.com` |
| `ADMIN_PATH` | Filament URL path — pick something non-obvious | `studio-7f3k` |
| `SESSION_SECURE_COOKIE` | `true` in production (HTTPS) | |
| `SETTINGS_CACHE_ENABLED` | `true` in production | |
| `RESPONSE_CACHE_ENABLED` / `RESPONSE_CACHE_LIFETIME` | Full-page cache switch / TTL in seconds | `true` / `604800` |
| `TURNSTILE_SITE_KEY` / `TURNSTILE_SECRET_KEY` | Optional Cloudflare Turnstile (then enable it in Admin → Spam protection) | — |

---

## Editing content (no code changes needed)

### Add a project
1. **Admin → Content → Projects → New project.**
2. **Identity tab**
   - *Anonymized title/summary* (EN + AR) — what the public sees by default. Describe the client generically ("B2B export platform for an international produce trader").
   - *Real title/summary, client name, live/repo URLs* — shown only when you reveal them.
   - *Client aliases* — every spelling, brand and domain of the client. The leak check uses them.
   - *Slug* — neutral and permanent (never the client's name). *Engagement type*: client (default), own product, or in-house.
3. **Case study tab** — challenge, solution, architecture, results (leave empty until you have verified outcomes), features, and **verified facts** (value + label + your private source note). Write these client-neutral: they are always public.
4. **Taxonomy tab** — categories (filters), technologies (filters + stack), related services (cross-links).
5. **Media tab**
   - *Cover / architecture diagram* — always public; must not identify the client. Without a cover, the site draws a generated "plate".
   - *Logo / screenshots* — stored on a **private disk** with random file names; they get a public URL only when the matching toggle is on.
6. **Visibility tab** — everything is off by default. Turn on *Published*, then only what the client approved: client name, live link, logo, screenshots, repo link. *Featured* puts it in the home bento grid; drag rows in the table to reorder.
7. **Save.** If a client name, alias or domain appears in public text while anonymized, you get a warning, and the dashboard launch checklist lists the project.

Images are converted to AVIF + WebP in several sizes on the queue (`php artisan queue:work`). The page cache clears automatically on save.

### Other content
- **Services** — copy, deliverables, process steps, FAQs and related projects per service.
- **FAQs** — general questions for the home page (service FAQs live on each service).
- **Testimonials** — stay hidden until *Placeholder* is off **and** *Published* is on. Only publish real quotes you have permission to use.
- **Experience** — the About page timeline; hidden until entries are published.
- **Pages** — About story and Privacy policy.
- **Inquiries** — contact-form submissions with status (new / read / replied / spam), reply-by-email and CSV export. Spam is deleted after 30 days, other inquiries after 24 months.

### Site Settings
- **Identity & hero** — name, title, location, time zone, working hours, availability, response time, years of experience (empty = never stated), hero headline/subline/CTA.
- **Identity → Portrait** — a photo on a dark background (lit face, black background works best). The photo stays on the private disk; the About page receives only a small grayscale light map cropped to the face and draws it with animated words. Remove it to hide the section. Wrap words of the headline in `*asterisks*` to show them in the accent colour.
- **Hiring & CV** — the *Open to roles* switch (hiring note in the hero, the "For companies hiring" card, the "Job or contract role" inquiry type) and the CV upload (PDF, EN + optional AR). Visitors download it from `/en/cv` / `/ar/cv` as `omar-khaled-CV-EN.pdf`; the download buttons appear only once a CV is uploaded, and replaced files are deleted.
- **Contact & social** — email, LinkedIn, GitHub, Upwork, Behance, booking link, WhatsApp. Values that are still placeholders (`contact@example.com`, `…/placeholder`, `+10000000000`) are **never shown publicly** and never reach JSON-LD, the sitemap or `llms.txt`.
- **Design** — logo (PNG/WebP/JPEG; optional dark-mode variant, otherwise it is recoloured for dark backgrounds) used in the header, footer and admin and to generate the favicons (on save, the upload is trimmed and turned into a ~10 KB WebP for the page, so any size of PNG is fine; a dark variant identical to the light one is ignored); accent colour, from which all accent tokens are derived to keep WCAG AA contrast in light and dark mode.
- **SEO** — indexing switch (turn off on staging), Google / Bing verification codes.
- **Spam protection** — Turnstile toggle.

The **dashboard launch checklist** lists every placeholder still in use plus advisory items (mail recipient, CV, logo, indexing, testimonials, experience, project results, leak check).

---

## Architecture notes

- **Anonymity by construction.** Public code reads projects only through `App\Presenters\PublicProject`, which applies the visibility toggles. `App\Support\Anonymity\AnonymityGuard` checks rendered HTML, JSON-LD, the sitemap and `llms*.txt` for client identifiers; the test suite runs it on every public page in both locales. Identifiable media sits on a non-web-accessible disk until revealed (`SyncProjectMediaVisibility`).
- **Placeholders by construction.** Identity and contact values are read only through `App\Support\Profile`, which drops placeholders.
- **Two middleware worlds.** Public pages (`routes/site.php`, `routes/static.php`) run without sessions or cookies and are full-page cached; only `/contact` (Livewire) and the admin use the `web` group.
- **CSP per surface.** Hash-based on public pages (no `unsafe-inline`), Livewire-compatible on `/contact`, Filament-compatible in the admin (`App\Http\Middleware\SecurityHeaders`).
- **Bilingual.** URL-prefixed locales, `lang/{en,ar}` UI strings (parity is tested), spatie/laravel-translatable content, logical CSS properties only (tested), Arabic written natively in Modern Standard Arabic.

## Tests

```bash
composer test
```

Covers locale routing, every public page × locale (one `h1`, title, description, canonical, hreflang, JSON-LD), the rendered-page leak check, placeholder handling, the contact form (validation in both languages, honeypot, time-trap, rate limits, mail routing with reply-to, localized auto-reply, Turnstile, pruning), sitemap, robots, llms.txt, security headers and CSP, the full-page cache and its invalidation, admin access + MFA, design-token contrast, and RTL/translation lint.

## License

Code © Omar Khaled. Fonts: Geist and Geist Mono (OFL 1.1), IBM Plex Sans Arabic (OFL 1.1) — licenses in `resources/fonts/`.
