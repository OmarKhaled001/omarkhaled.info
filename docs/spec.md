# omarkhaled.info — Product & Technical Spec

_Status: **DRAFT — awaiting approval.** No application code is written until this is approved._
_Date: 2026-10-06 · Source data: [`projects-inventory.md`](projects-inventory.md)_

---

## 0. Decisions already locked

| Topic | Decision |
|---|---|
| Framework | **Laravel 13**, PHP 8.4, **Filament 5**, **Livewire 4** (Alpine ships inside Livewire), Tailwind CSS v4, Vite, Pest 4 |
| Repo | Fresh repo at `/home/mora-khaled/omarkhaled.info/`. **No file from any client project is ever copied in** except approved screenshots, which live in a gitignored folder and enter the app only through the media library. |
| Editable identity | All contact/identity values live in Filament **Site Settings** (spatie/laravel-settings), seeded with clearly marked placeholders. |
| Client confidentiality | Every project defaults to **anonymized**; per-project toggles control what is revealed. |
| Unverified claims | No invented metrics, testimonials or experience numbers. Only verifiable facts are seeded; `results` stays empty until you fill it. |
| Multi-tenancy | **Not claimed** anywhere on the site. |

---

## 1. Sitemap & URLs

All public pages exist in both locales under a prefix. Slugs are **ASCII and shared across locales** (`/ar/projects/b2b-export-platform`), which keeps the language switcher, `hreflang`, and sitemap alternates trivial and avoids percent-encoded Arabic URLs. **Slugs never contain client names** (they are neutral by default and stay stable when a project is de-anonymized, so no redirects are ever needed).

| Path | Page | Cached |
|---|---|---|
| `/` | 302 → `/en` or `/ar` from `Accept-Language` (fallback `en`), `Vary: Accept-Language` | no |
| `/{locale}` | Home | ✅ |
| `/{locale}/about` | About | ✅ |
| `/{locale}/services` | Services index | ✅ |
| `/{locale}/services/{slug}` | Service detail (×6) | ✅ |
| `/{locale}/projects` | Projects index, filter by `?type=` / `?tech=` (plain GET links) | ✅ (query string is part of the cache key) |
| `/{locale}/projects/{slug}` | Case study | ✅ |
| `/{locale}/contact` | Contact (Livewire form) | ❌ (CSRF/session) |
| `/{locale}/privacy` | Privacy policy | ✅ |
| any unknown path | Localized 404 (locale inferred from prefix, else `Accept-Language`) | — |
| `/sitemap.xml` | XML sitemap with `xhtml:link` alternates | ✅ |
| `/robots.txt` | Dynamic (no static file in `public/`) | ✅ |
| `/llms.txt`, `/llms-full.txt` | GEO summaries (Markdown, `text/plain; charset=utf-8`) | ✅ |
| `/{ADMIN_PATH}` | Filament panel (default `admin`, configurable via env) | ❌ |

Service slugs: `laravel-development`, `saas-development`, `ecommerce-development`, `filament-admin-panels`, `api-integrations`, `performance-security-audits`.

Project slugs (neutral): `b2b-export-platform`, `apparel-design-marketplace`, `dental-scan-saas`, `travel-booking-platform`, `multilingual-corporate-cms`, `client-operations-panel`; drafts: `qr-code-saas`, `volunteer-management-system`, `print-on-demand-platform`.

Filtered project-index views (`?type=…`) render `<meta name="robots" content="noindex,follow">` with canonical → unfiltered index, so they never compete with the main page.

---

## 2. Page sections

### 2.1 Home
1. **Hero** — H1 value proposition for international companies, short supporting line, primary CTA **Start a project** (text from settings, EN/AR) → `/contact`, secondary **See selected work** → `/projects`. Availability chip ("Available for new projects · replies within 24 h") rendered only when the setting isn't a placeholder. The hero is text-only so LCP is a text node (no hero image).
   - Draft EN H1: _"Laravel platforms and back-offices for teams that need them to just work."_
   - Draft AR H1: _"أبني منصّات Laravel وأنظمة إدارة تعمل بثبات، لفرق العمل حول العالم."_
2. **Trusted stack** — monochrome inline-SVG row: Laravel, Filament, Livewire, PHP, MySQL, Tailwind, Alpine, Redis, Docker. Only technologies used in published projects (driven by the `technologies` table).
3. **Featured services** — 6 cards → service pages.
4. **Featured projects** — bento grid of `is_featured` projects (see §4.6). Anonymized projects use generated "plates" instead of screenshots.
5. **Process** — Discover → Design → Build → Launch & support, each with what the client receives (scope doc, clickable design, weekly demos on staging with automated tests, handover docs).
6. **Testimonials** — **hidden entirely** until at least one testimonial is published and not a placeholder.
7. **FAQ** — question-style, native `<details>` (zero JS), FAQPage schema.
8. **Contact CTA** — short pitch + button to `/contact` + response-time promise from settings.

### 2.2 About
Story ("From pixels to production": print/graphic design → full-stack), how the design background shows up in the work, **skills grouped by domain** (Backend, Frontend, Admin & data, Integrations, Infrastructure, Design), **experience timeline** (rendered only for published entries), working style with remote global clients (async updates, weekly demos, English/Arabic), **time-zone availability** computed from settings (e.g. "Cairo, UTC+2/+3 — full overlap with Europe and the Gulf, mornings for US East"), and a CTA.

### 2.3 Services index + 6 service pages
Each service page: H1 + intro → **The problem** → **What you get** (deliverables list) → **How we'll work** (process steps) → **Related projects** (via pivot, presenter-aware) → **FAQ** (FAQPage schema) → CTA. Cross-links: service ↔ projects ↔ other services.

| Service | Related projects (seed) |
|---|---|
| Laravel development | B2B export platform, apparel marketplace, travel booking platform, dental-scan SaaS |
| SaaS product development | dental-scan SaaS (own product). No multi-tenancy claims. |
| E-commerce development | apparel marketplace, B2B export platform |
| CRM & admin panels with Filament | B2B export platform, apparel marketplace, travel booking platform, multilingual CMS |
| REST APIs & integrations | dental-scan SaaS (Python geometry service), apparel marketplace (Paymob, OAuth, JSON API), B2B export platform (Gemini, carrier tracking) |
| Performance & security audits | dental-scan SaaS (measured sizing), B2B export platform (AI security tests), client-operations panel (encryption, RBAC, audit log). Framed as a capability backed by these practices, not as past audit engagements. |

### 2.4 Projects index
Filter chips for **type** (E-commerce, B2B platform, SaaS, Booking, Corporate & CMS, Internal tool) and **technology**, implemented as server-rendered links with `aria-current`, so the page works without JS and crawlers see every project. Cards show the presenter-resolved title, summary, type, year, and top 3 technologies.

### 2.5 Case study
Hero (title, summary, role, engagement type, year, industry, live link if allowed) → **Challenge** → **Solution** → **Architecture** (text + optional diagram) → **Key features** → **Results** (only when filled; otherwise the verified **Facts** strip stands alone) → **Gallery** (only if `show_screenshots`) → **Tech stack** → **Next project**.

### 2.6 Contact
Form (§7) + email (if not placeholder) + social links (non-placeholder only) + expected response time + time-zone note.

### 2.7 404 & Privacy
- **404**: localized, with personality and the print-heritage motif. EN: _"This page didn't make it to print."_ with misaligned crop marks and links to Home / Work / Contact. AR equivalent written natively. Status 404, `noindex`.
- **Privacy**: what the contact form collects, why, retention (auto-pruned, §7), mail provider, optional Cloudflare Turnstile, **no tracking cookies on public pages** (§6), contact for deletion requests. Editable in Filament (`pages` table).

---

## 3. Content model

All content text fields are JSON-translatable (`spatie/laravel-translatable`, locales `en`, `ar`). Indexes are listed per table.

### 3.1 Tables

**`projects`**
| Column | Type | Notes |
|---|---|---|
| `slug` | string, unique | neutral, ASCII |
| `title`, `anonymized_title` | json (tr) | real vs. anonymized |
| `summary`, `anonymized_summary` | json (tr) | card/meta description, real vs. anonymized |
| `client_name` | json (tr) | e.g. EN "Cairo Key" / AR "كايرو كي" |
| `client_aliases` | json array | extra strings for the leak guard (domains, brand spellings) |
| `industry`, `role` | json (tr) | role e.g. "Sole full-stack developer & designer" |
| `challenge`, `solution`, `architecture`, `results` | json (tr), rich text, nullable | long-form fields are **always written client-neutral**; `results` empty until you fill it |
| `engagement_type` | enum `solo`/`client`/`employer`, default `client` | |
| `schema_type` | enum `CreativeWork`/`SoftwareApplication`, default `CreativeWork` | ArchPrep = `SoftwareApplication` |
| `live_url`, `repo_url` | string, nullable | |
| `year` | smallint | |
| `show_client_name`, `show_live_link`, `show_logo`, `show_screenshots`, `show_repo_link` | bool, default **false** | `show_repo_link` added because all client repos are private |
| `is_published`, `is_featured` | bool, default false | |
| `sort_order` | int | |
| `meta_title`, `meta_description` | json (tr), nullable | optional overrides; otherwise derived from presenter |
| Indexes | `(is_published, sort_order)`, `(is_published, is_featured, sort_order)`, unique `slug` |

**`project_features`**: `project_id`, `title` (tr), `body` (tr), `sort_order` · index `(project_id, sort_order)`
**`project_facts`**: `project_id`, `label` (tr), `value` (string, e.g. "294"), `source_note` (admin-only, e.g. "Pest count, git 2026-10-06"), `sort_order`. These are the verified numbers.
**`categories`** (project types): `slug` unique, `name` (tr), `sort_order`; pivot `category_project` (composite PK).
**`technologies`**: `slug` unique, `name`, `domain` enum (backend/frontend/admin-data/integrations/infrastructure/design), `icon` key, `show_on_about` bool, `sort_order`; pivot `project_technology` (composite PK, `sort_order`).
**`services`**: `slug` unique, `icon`, `title`, `card_summary`, `headline`, `intro`, `problem`, `cta_text` (all tr), `meta_title`/`meta_description` (tr), `is_published`, `sort_order` · index `(is_published, sort_order)`.
**`service_items`**: `service_id`, `kind` enum `deliverable`/`process_step`, `title` (tr), `body` (tr), `sort_order` · index `(service_id, kind, sort_order)`.
**`project_service`** pivot (composite PK, `sort_order`).
**`faqs`**: nullable morph `faqable` (service, or null = home/general), `question` (tr), `answer` (tr), `is_published`, `sort_order` · index `(faqable_type, faqable_id, is_published, sort_order)`.
**`testimonials`**: `author_name`, `author_role` (tr), `company` (tr), `quote` (tr), `project_id` nullable, `is_placeholder` bool (default true for seeds), `is_published` bool (default false), `sort_order`. **Filament refuses to publish a testimonial while `is_placeholder` is true.**
**`experiences`**: `company`, `role` (tr), `description` (tr), `started_on`, `ended_on` (nullable dates), `is_current`, `is_published` (seeded **false**, since dates are unconfirmed), `sort_order`.
**`pages`**: `key` unique (`about`, `privacy`), `title` (tr), `body` (tr, rich), `meta_title`/`meta_description` (tr).
**`contact_submissions`**: `name`, `email`, `company`, `project_type`, `budget_range`, `message`, `locale`, `ip_hash` (HMAC-SHA256 of IP with app key; the raw IP is never stored), `user_agent` (truncated), `status` enum `new`/`read`/`replied`/`spam`, `spam_reason` nullable, timestamps · indexes `(status, created_at)`, `email`.
**`users`**: stock + `is_admin` bool. There is no public registration.

### 3.2 Media (spatie/laravel-medialibrary)

| Model | Collection | Identifiable? | Disk |
|---|---|---|---|
| Project | `logo` | yes | `media_private` until `show_logo` |
| Project | `screenshots` (gallery, each with per-locale alt text + `device` property) | yes | `media_private` until `show_screenshots` |
| Project | `cover` (optional custom non-identifiable visual) | no | `public` |
| Project | `architecture` (diagram) | no | `public` |
| Testimonial | `avatar` | — | `public` |
| Settings | `profile_photo`, `default_og` | — | `public` |

- **Confidentiality by storage, not just by template:** identifiable collections live on a **non-web-accessible disk**. A queued `SyncProjectMediaVisibility` job moves them to `public` when a toggle is switched on, and back when it's switched off. Hidden screenshots therefore have **no public URL at all**.
- **File names** are UUIDs (custom `FileNamer` + `PathGenerator`), so URLs never leak client names. EXIF data is stripped.
- **Conversions:** responsive sizes (480/768/1200/1600/2400w) in **AVIF + WebP** with a JPEG/PNG fallback, generated on a queue. AVIF is enabled only if GD/Imagick on the host supports it (runtime check; WebP-only fallback).
- **Rendering:** a `<x-picture>` component outputs `<picture>` with AVIF/WebP `srcset` + `sizes`, explicit `width`/`height`, `loading="lazy"` + `decoding="async"` by default, and `fetchpriority="high"` for the one above-the-fold image.

### 3.3 Settings (spatie/laravel-settings + Filament settings pages)

| Class | Fields (seed value) |
|---|---|
| `IdentitySettings` | `person_name` {en: "Omar Khaled", ar: "عمر خالد"}, `job_title` {en/ar}, `location` {en: "Egypt", ar: "مصر"}, `timezone` "Africa/Cairo", `working_hours` "10:00–19:00", `availability_status` enum available/limited/booked (`available`), `availability_note` {en/ar}, `response_time_hours` 24, `years_experience` **null** (copy omits the number until set), `hero_headline` {en/ar}, `hero_subheadline` {en/ar}, `hero_cta_text` {en: "Start a project", ar: "ابدأ مشروعك"} |
| `ContactSettings` | `contact_email` **contact@example.com**, `upwork_url` **https://www.upwork.com/freelancers/placeholder**, `linkedin_url` https://www.linkedin.com/in/omar-khaled-890b14396 (real), `github_url` https://github.com/OmarKhaled001 (real), `whatsapp` **+10000000000**, `behance_url` null, `calendly_url` null |
| `SeoSettings` | `title_suffix` {en: "Omar Khaled", ar: "عمر خالد"}, `default_description` {en/ar}, `indexing_enabled` (false on staging), `google_site_verification`, `bing_site_verification` |
| `SpamSettings` | `turnstile_enabled` false (keys come from `.env`, never from the DB) |

**Placeholder handling**
- `PlaceholderDetector` treats a value as a placeholder if it is empty, matches `config('portfolio.placeholders')` (e.g. `@example.com`, `/placeholder`, `+10000000000`), or contains the literal marker `[placeholder]`.
- Every public consumer goes through `Profile::publicValue()`, which returns `null` for placeholders. So placeholder values **never** reach JSON-LD `sameAs`/`email`, the sitemap, `llms.txt`, OG images, mail headers, **or visible pages**. (The prompt only required the first three; hiding them from visible pages too avoids shipping `contact@example.com` publicly. In `local` env a dashed "placeholder" badge shows instead so you can see what's missing.)
- **Mail recipient:** `ContactSettings.contact_email` if it isn't a placeholder, else `MAIL_CONTACT_ADDRESS` from `.env`. If both are missing, the submission is still stored and the dashboard shows a red alert.
- **Launch checklist widget** (Filament dashboard): lists every setting still holding a placeholder (with a deep link to the field), plus advisory items: mail recipient source, `indexing_enabled` off, testimonials all unpublished, projects with empty `results`, unpublished experience entries, and any project whose leak-guard check fails (§4.2).

---

## 4. Anonymity layer (the core business rule)

### 4.1 One choke point
All public rendering of a project goes through `PublicProject` (a presenter/view model). Templates, JSON-LD, sitemap, `llms*.txt`, OG images and alt text **never read model attributes directly**.

| Output | Anonymized (default) | Revealed |
|---|---|---|
| Title / summary | `anonymized_title` / `anonymized_summary` | `title` / `summary` (requires `show_client_name`) |
| Client name in hero, schema `sourceOrganization` | omitted | `client_name` (requires `show_client_name`) |
| Live link | omitted | `live_url` (requires `show_live_link`) |
| Logo | omitted | `logo` media (requires `show_logo`) |
| Gallery | omitted; the generated plate is shown | `screenshots` (requires `show_screenshots`) |
| Repo link | omitted | `repo_url` (requires `show_repo_link`) |
| Alt text | generic ("Admin dashboard of a B2B export platform") | per-image alt |

### 4.2 Leak guard
`AnonymityGuard` checks rendered output (HTML, JSON-LD, `llms*.txt`, sitemap, OG source text) for `client_name` (all locales), `client_aliases`, and the hostname of `live_url`, case-insensitively and Arabic-normalized. It runs:
- in **feature tests** for every seeded project;
- on **save in Filament** (warning notification + launch-checklist item).

This catches a client name typed into a neutral long-form field by mistake.

### 4.3 Seeded anonymized titles

| Slug | Anonymized title (EN) | (AR) |
|---|---|---|
| b2b-export-platform | B2B export platform for an international produce trader | منصة تصدير B2B لشركة تجارة منتجات زراعية دولية |
| apparel-design-marketplace | Story-driven apparel store with a custom-design marketplace | متجر أزياء قائم على القصص مع سوق للتصاميم المخصّصة |
| dental-scan-saas | Dental scan preparation SaaS with a 3D review workspace | منصة SaaS لتجهيز مسوح الأسنان مع مساحة مراجعة ثلاثية الأبعاد |
| travel-booking-platform | Bilingual booking platform for a tourism company in Egypt | منصة حجوزات ثنائية اللغة لشركة سياحة في مصر |
| multilingual-corporate-cms | Six-language corporate website and CMS for a UAE food trader | موقع مؤسسي بست لغات مع نظام إدارة محتوى لشركة تجارة أغذية في الإمارات |
| client-operations-panel | Secure client-operations panel for a web studio | لوحة تشغيل آمنة لإدارة عملاء استوديو ويب |

### 4.4 Seeded verified facts (from code/git, dated 2026-10-06)
- **B2B export platform**: 20 admin modules · 27 automated test files · 2 languages · 2 AI assistants · 5 sea-carrier tracking integrations
- **Apparel marketplace**: 294 automated tests · 18 admin modules · 14-stage design workflow · 2 languages
- **Dental-scan SaaS**: 268 automated tests (197 PHP + 67 Python + 4 JS) · meshes up to 1M faces per arch · live progress over websockets
- **Travel booking**: 7 admin modules · 2 languages · Google sign-in
- **Multilingual CMS**: 6 languages · 8 admin modules · per-page SEO in every language
- **Client-ops panel**: AES-256-GCM encryption at rest · 9-state request workflow · full audit log

### 4.5 Seeded projects
- **Published, anonymized:** the 6 real projects (Petrogina, NARRVA, ArchPrep, Cairo Key, Ludic, Client Control Panel), `engagement_type = client`. ArchPrep is the exception: `solo`, `schema_type = SoftwareApplication`, own product.
- **Featured:** B2B export platform, apparel marketplace, dental-scan SaaS, travel booking platform, multilingual CMS.
- **Drafts** (`is_published = false`) with whatever info exists: QRX (QR-code SaaS), Resala VMS (volunteer management), Printalia (print-on-demand platform, Yemen). "ERP & POS" is not seeded, since there is no concrete project behind it.
- **Media import:** the `PortfolioMediaSeeder` imports `storage/app/seed-media/screenshots/*` (from `manifest.json`) into the private `screenshots` collection when the folder exists. The folder is gitignored, so on production you upload through Filament or run `php artisan portfolio:import-media <path>`.
- **ArchPrep visuals** come only from a local run with **synthetic** meshes (§13, step 12). Real scans are never used.

### 4.6 Generated project "plates"
Anonymized cards need visuals that don't identify the client. Each project gets a server-rendered **inline SVG plate**: its index number in large type, an industry glyph, and a mini architecture diagram (nodes and edges from its tech stack), in the site's accent on a registration-mark grid. It weighs nothing, is crisp at any size, is on-brand for a designer, and is identity-safe. A custom `cover` upload overrides it.

---

## 5. Design system

**Concept: "From pixels to production."** The design leans on your print-design origin: registration and crop marks, a fine blueprint grid, mono "slug" labels like a print job ticket, and one confident accent. This avoids the generic purple-gradient developer template and quietly tells the designer-who-codes story.

### 5.1 Tokens (CSS variables, Tailwind v4 `@theme`)
| Token | Dark (default when the system is dark) | Light |
|---|---|---|
| `--bg` | `#0A0A0B` ink | `#F7F6F2` paper |
| `--surface` | `#111113` | `#FFFFFF` |
| `--surface-2` | `#17171A` | `#EFEDE7` |
| `--border` | `#26262B` | `#E2DFD6` |
| `--text` | `#EDEDEF` | `#131316` |
| `--muted` | `#A1A1AA` | `#5B5B63` |
| `--accent` (fills, marks) | `#FF6A3D` vermilion | `#E4572E` |
| `--accent-text` (accent used as text) | `#FF8A66` | `#B83A12` |
| `--on-accent` | `#0A0A0B` | `#FFFFFF` |
| `--focus` | `#FF8A66` | `#B83A12` |

- A unit test computes the WCAG contrast of every text/background token pair in both themes and fails below 4.5:1 for text and 3:1 for UI and focus indicators.
- Other tokens: radius `6/10/16`; spacing on a 4 px base; motion `--ease-out: cubic-bezier(.2,.7,.2,1)`, durations 160/240/400 ms.
- Texture: a 3 %-opacity SVG noise overlay plus a radial-faded grid, both CSS-only. Glass (`backdrop-blur`) is used **only** on the sticky header.

### 5.2 Typography
- **Latin:** Geist (variable) for UI and display, Geist Mono for labels, indices and code.
- **Arabic:** IBM Plex Sans Arabic 400/500/700. It is clear at small sizes, has professional weights, and pairs proportionally with Geist.
- **Self-hosted** WOFF2, subset with `pyftsubset`: Latin + punctuation for Geist; Arabic + Arabic presentation forms + basic Latin for Plex. `unicode-range` declarations mean Arabic glyphs only download on pages that need them.
- `font-display: swap`; **preload only the current locale's primary text face**; metric-matched fallback faces (`size-adjust`, `ascent-override`) prevent CLS.
- **Fluid scale:** display `clamp(2.5rem, 6vw, 5.5rem)` with tracking −0.035em (Latin only; Arabic gets 0 tracking and +10 % line-height); body 1rem/1.65 (Arabic 1.8).

### 5.3 Layout & components
- **Layout:** 12-column grid, max content width 1200 px, gutters 16/24/32 px, mobile-first, verified at 360 px with no horizontal scroll.
- **Bento (featured projects):** desktop is 6 columns (flagship 4×2, then 2×1, 2×1, 3×1, 3×1); tablet is 2 columns; mobile is 1.
- **Cards:** crop marks appear at the corners on hover/focus, with an accent hairline.
- **Section headers:** mono index slugs (`01 — Services`).
- **Icons:** Lucide via blade-icons, cached. Directional icons (arrows, chevrons) mirror in RTL via `rtl:-scale-x-100`; non-directional ones (check, search) don't.
- **RTL:** logical properties only (`ms-/me-/ps-/pe-/start-/end-`, `text-start`). A lint test greps Blade for `ml-|mr-|pl-|pr-|left-|right-|text-left|text-right` and fails the build if any appear.

### 5.4 Theme
- Dark/light follows the system by default. A toggle stores `light`/`dark` in `localStorage` (per-viewer convenience) and sets `data-theme` on `<html>`.
- A ~300-byte inline `<head>` script applies the theme before paint, so there is no flash. Its SHA-256 hash is whitelisted in CSP.
- Without JS, the `prefers-color-scheme` media query still applies.

### 5.5 Motion
- Scroll reveals use a tiny IntersectionObserver module that only activates when `html.js` is present, so **content is fully visible without JS** (crawlers, AI bots, no-JS users).
- Animations are limited to opacity and translate, 240–400 ms.
- `prefers-reduced-motion: reduce` disables reveals and hover translations.
- No scroll-jacking and no smooth-scroll libraries.

### 5.6 Accessibility (WCAG 2.2 AA)
- **Structure:** skip link; landmarks (`header`, `nav`, `main`, `footer`); exactly one H1 per page and no skipped levels (tested); `lang` and `dir` on `<html>`.
- **Focus:** visible 2 px focus ring with offset using `--focus`; sticky-header-aware `scroll-padding-top` so focused elements are never obscured (2.4.11).
- **Pointers:** targets ≥ 24×24 px (2.5.8).
- **Mobile menu:** a `<dialog>`, with Esc to close and focus return.
- **Forms:** labels, `aria-describedby` errors, an `aria-live` status region, and `autocomplete` attributes.
- **Images:** alt text on all of them; decorative SVGs get `aria-hidden`.
- **Language:** the language switcher marks each link with `lang`/`hreflang`.
- **Colour:** never the only carrier of meaning.

---

## 6. Front-end architecture & performance

- **Server-rendered Blade for every public page.** No content depends on JS.
- **JS budget for public pages:** one vanilla ES module (~3 KB gzipped) for the theme toggle, mobile menu and reveals. **Livewire and Alpine load only on `/contact`** and in Filament. FAQs use native `<details>`, filters are links, and the language switcher is links.
- **CSS:** a single Tailwind v4 bundle (target < 30 KB gzipped).

**Public middleware group `public`**
- Includes: `SetLocale`, `SecurityHeaders`, `CacheResponse`.
- Excludes: **no session, no cookies, no CSRF.** Public pages therefore set **zero cookies**, cache cleanly, and need no consent banner.
- `/contact` uses the full `web` group.

**Full-page cache (spatie/laravel-responsecache)**
- A custom `CacheProfile` caches guest GET 200 responses on `public` routes, keyed by URL + query.
- Cached responses carry `Cache-Control: public, max-age=300, stale-while-revalidate=86400` (CDN-friendly).
- The cache is cleared on save/delete of any content model or settings, via a model observer and a settings event.

**Other performance work**
- **Laravel optimization:** `optimize` (config/route/view/event cache) and `icons:cache` in deploy; OPcache.
- **N+1 queries:** `Model::preventLazyLoading()` outside production, so tests fail on any N+1. Explicit eager loading everywhere (`with(['categories','technologies','media'])`), and query counts asserted in key page tests.

**Core Web Vitals targets** (mobile, production build): LCP < 1.8 s (text LCP, preloaded font, no render-blocking JS), CLS < 0.05 (dimensions on all media, font metric overrides), INP < 150 ms (near-zero JS), and Lighthouse ≥ 95 in all four categories.

---

## 7. Contact form

- **Livewire 4 component `ContactForm`** on `/contact` only.
- **Fields:**
  - name (required, ≤ 100)
  - email (required, `email:rfc,dns`)
  - company (optional, ≤ 120)
  - project type (select: Laravel web app · SaaS product · E-commerce · CRM / admin panel · API / integration · Performance or security audit · Other)
  - budget range in USD (select: < 3k · 3–7.5k · 7.5–15k · 15–30k · 30k+ · Not sure yet)
  - message (required, 20–5000)
  - Options live in `config/portfolio.php`; labels come from lang files.
- **Anti-spam layers:**
  1. **Honeypot:** a visually hidden field that is not `display:none` (bots skip those), with `tabindex=-1` and `autocomplete=off`. If it's filled, the user sees a fake success and the submission is stored as `spam`, with no mail sent.
  2. **Time-trap:** an encrypted timestamp issued at mount. Submissions under 3 s or older than 2 h are rejected the same way.
  3. **Rate limiting:** per IP, 3 per 10 min and 10 per day, plus per email address, 3 per day. Over the limit, the user sees a localized "try again in N minutes" message.
  4. **Optional Cloudflare Turnstile:** enabled when `TURNSTILE_SITE_KEY`/`TURNSTILE_SECRET_KEY` are set and the setting is on. Server-side verification via `siteverify` (tested with `Http::fake`); CSP extended for `challenges.cloudflare.com` on this page only.
- **On success**, the `SubmitInquiry` action:
  1. stores the `ContactSubmission`;
  2. queues `NewInquiryMail` to the resolved recipient (§3.3) with **reply-to = the sender**, sent from `MAIL_FROM_ADDRESS`. The From address must be a domain you control (SPF/DKIM), never the sender's address;
  3. queues `InquiryReceivedMail` to the sender **in the locale they used** (RTL template for Arabic).
- **Auto-reply anti-abuse:** the auto-reply never echoes the user's message or company. It only greets them by first name (escaped), so the form can't be used as a spam relay to arbitrary addresses. It is also limited to one per address per 24 h.
- **Queue:** the `database` driver (host-agnostic). On a VPS, run a supervisor worker; on shared hosting, the scheduler runs `queue:work --stop-when-empty` every minute. Both are documented in the README.
- **States:** inline validation errors, a loading state on the submit button, and success/error panels, all in both languages and announced through `aria-live`.
- **Filament `ContactSubmissionResource`:**
  - read-only, with status actions (mark read / replied / spam) and a "Reply" `mailto:` action;
  - filters by status, type, budget and date; CSV export;
  - a dashboard stat widget for new submissions this week.
- **Retention:** `ContactSubmission` is `MassPrunable`. Spam is deleted after 30 days and everything else after 24 months (stated in the privacy policy), via scheduled `model:prune`.

---

## 8. Admin panel (Filament 5)

- **Access:** login only, at `/{ADMIN_PATH}`.
  - **MFA required** via Filament's built-in app authentication.
  - `canAccessPanel()` requires `is_admin`.
  - Admins are created with `php artisan portfolio:make-admin`, which prompts for the password and never stores it in code or the seeder.
  - Login is throttled.
  - `X-Robots-Tag: noindex` on the panel. The path is **not** listed in robots.txt, so it isn't advertised.
- **Resources:**
  - **Projects:** EN \| AR side-by-side fields, a **Visibility** section with all toggles and live hints, media managers per collection, repeaters for features and facts, and relation pickers for categories, technologies and services.
  - Services (with items and FAQs), Categories, Technologies, Testimonials, FAQs (general), Experiences, Pages, Contact submissions.
- **Settings pages:** Identity, Contact & social, SEO, Spam.
- **Dashboard:** launch checklist, new inquiries, content health (unpublished counts).
- **Translatable fields:** a small in-house helper renders `field.en` / `field.ar` side by side and syncs them via `getTranslations`/`setTranslations`. Side-by-side editing is better for writing both languages, and it avoids depending on a third-party translatable plugin's Filament 5 support.

---

## 9. SEO plan (Google)

### 9.1 Per-page metadata
A `Seo` value object is built in each controller and rendered by `<x-seo>`.

**Titles and descriptions**
- Unique `<title>` and `<meta name="description">` per page and per locale.
- Pattern: `{Page} — Omar Khaled` / `{Page} — عمر خالد`.
- Home: _"Omar Khaled — Laravel & Filament Developer for Global Teams"_.

**Canonical and alternates**
- Self-referencing canonical.
- `hreflang` `en`, `ar`, and `x-default` (→ `/en/...`).

**Open Graph and Twitter**
- OG `title`/`description`/`url`/`type`/`locale` (+ `og:locale:alternate`), and `og:image` at 1200×630 with alt text.
- `twitter:card = summary_large_image`.

**Indexing controls**
- `robots` `index,follow` by default.
- Staging (`indexing_enabled=false`) sends `noindex` and a `Disallow: /` robots.txt.

**Verification**
- Google/Bing verification metas come from settings.

**Structure and linking**
- One H1 per page, logical heading order, semantic URLs.
- Internal links: service ↔ projects, project → services, breadcrumbs on deep pages.

### 9.2 Structured data (single `@graph` per page, JSON-LD, built with `spatie/schema-org`)
- `Person` `#person`: name, jobTitle, address (country only), `knowsAbout` (from technologies), `knowsLanguage` [en, ar], `sameAs` = non-placeholder social URLs, `image` if a photo exists.
- `ProfessionalService` `#service-business`: name, description, `provider` → `#person`, `areaServed: "Worldwide"`, `availableLanguage`, `url`.
- `WebSite` `#website`: `inLanguage`, `publisher` → `#person`.
- `BreadcrumbList` on every non-home page.
- Case study: `CreativeWork`, or `SoftwareApplication` for `schema_type`. Fields: `name`/`description` from the presenter, `creator` → `#person`, `keywords` (tech), `dateCreated`, `url`, `image` (only non-identifiable or revealed), `sourceOrganization` only when `show_client_name`.
- Service page: `Service` with `provider` → `#person`, `serviceType`, `areaServed`, and an `isRelatedTo` link to each related case study.
- `FAQPage` on any page with published FAQs. Google limits FAQ rich results to authoritative sites since 2023, so the value here is mainly AI and semantic understanding; it costs nothing.
- **A test parses every page's JSON-LD and asserts** valid JSON, the expected types, no placeholder values, and no client identifiers for anonymized projects.

### 9.3 Sitemap & robots
- **`sitemap.xml`:** every published page × 2 locales, each with `xhtml:link` alternates (en, ar, x-default) and `lastmod` = `updated_at`. Excludes drafts, filtered views and the admin panel. Cached and cleared with the response cache.
- **`robots.txt`:**
  - `Allow: /` for `*`, plus explicit `Allow` groups for GPTBot, OAI-SearchBot, ChatGPT-User, ClaudeBot, Claude-SearchBot, Claude-User, PerplexityBot, Perplexity-User, Google-Extended, Applebot-Extended, Bingbot.
  - `Sitemap: https://omarkhaled.info/sitemap.xml`.

### 9.4 OG images (auto-generated)
- A queued `GenerateOgImage` job runs on save of a project, service or page, once per locale.
- It renders 1200×630 PNGs with **Intervention Image v3 (GD)** and **ar-php** for correct Arabic glyph shaping and RTL ordering. This needs no headless Chrome, so it works on any host.
- Layout: the plate motif, presenter title, mono slug, and name/role footer.
- Files are stored in `public/og/` under content-hash names; there is a default OG from settings as a fallback.
- An anonymized project's OG image uses its anonymized title.

---

## 10. GEO plan (AI search)

- **`/llms.txt`** (spec: llmstxt.org), generated from the DB and settings and cached:
  - H1 with the name, then a blockquote summary.
  - One factual paragraph: who you are, location, stack, who you work with.
  - Sections: **Services** (each linked, one line each), **Selected work** (published projects via the presenter, each linked with a one-line factual summary and the verified facts), **About**, **Contact** (non-placeholder channels only), **Optional** (Arabic versions).
- **`/llms-full.txt`:** the full English text of every service page, case study and FAQ, in clean Markdown with canonical URLs.
- **Writing rules for all copy** (EN and AR):
  - Entity-first, quotable sentences, e.g. _"Omar Khaled is a full-stack Laravel and Filament developer based in Egypt who builds e-commerce platforms, B2B portals, and admin systems for companies worldwide."_
  - Specific nouns (Laravel 13, Filament 5, Paymob, Gemini), verified numbers only, and no superlatives that can't be backed.
- **Entity consistency:** name, title, location and `sameAs` come from **one source** (settings), so schema, page copy, `llms.txt` and OG text can't drift.
- **Question-style FAQs** with direct first-sentence answers:
  - _"Where can I hire a Laravel developer for a SaaS product?"_
  - _"Can you build a Filament admin panel for our existing Laravel app?"_
  - _"Do you work with companies in Europe, the US, and the Gulf?"_
  - _"How do you handle time zones?"_
  - _"Can you take over an existing Laravel codebase?"_
  - _"Do you sign NDAs?"_
  - _"How do projects start and how are they priced?"_
- **Arabic copy:** Modern Standard Arabic, professional and natural (not Egyptian colloquial, which the old portfolio used), written natively rather than translated.

---

## 11. Security

- **Headers** (`SecurityHeaders` middleware):
  - CSP (§11.1), HSTS (`max-age=63072000; includeSubDomains; preload`, production + HTTPS only), `X-Frame-Options: DENY` + `frame-ancestors 'none'`.
  - `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` (camera, mic, geolocation, etc. disabled), `Cross-Origin-Opener-Policy: same-origin`.
- **11.1 CSP strategy:**
  - Cached public pages can't use per-request nonces, because the cached nonce would mismatch the header. They therefore use `script-src 'self' 'sha256-<theme-script>'`, `style-src 'self'`, `img-src 'self' data:`, `font-src 'self'`, `connect-src 'self'`, `base-uri 'self'`, `form-action 'self'`, `object-src 'none'`.
  - `/contact` is uncached and gets a **nonce-based** CSP compatible with Livewire 4 (+ Turnstile if enabled).
  - The Filament panel gets its own CSP (it needs `'unsafe-eval'` for Alpine expressions) since it sits behind MFA.
  - Verified in tests and in the browser console during QA.
- **Other measures:**
  - Validation on every input, CSRF on the contact form, rate limiting, and mass-assignment guarded via `$fillable`.
  - Signed/queued jobs only. No secrets in code; `.env.example` contains **only empty or placeholder values**, and a test asserts it holds no non-empty secret keys (the lesson from the Petrogina leak).
  - Secure session cookies in production.
  - `composer audit` and `npm audit` in CI.

---

## 12. Testing (Pest 4)

| Area | Tests |
|---|---|
| Locale routing | `/` → `/ar` for `Accept-Language: ar-EG`, → `/en` for `fr`/missing; unsupported prefix → 404; `<html lang dir>` correct; the switcher link keeps the same page and query |
| Key pages | every public page × locale returns 200 with a unique title, a meta description, a self canonical, hreflang en/ar/x-default, exactly one H1, and valid JSON-LD |
| Contact | validation per field (both locales); honeypot → fake success, stored as spam, no mail; time-trap; rate limit (4th request blocked); `Mail::fake` asserts the queued owner mail with reply-to sender, the auto-reply in the submission locale, recipient fallback to `MAIL_CONTACT_ADDRESS` when the setting is a placeholder, and a stored submission; Turnstile pass/fail with `Http::fake` |
| Sitemap | both locales with alternates; excludes drafts and unpublished projects; valid XML |
| robots / llms | AI bots allowed; sitemap line; no placeholders; anonymized projects described with anonymized titles only |
| Anonymity | every seeded project with default toggles: the leak guard passes on the case study, index, home, JSON-LD, sitemap and `llms*.txt`; toggling `show_client_name`/`show_live_link` reveals exactly those fields; hidden screenshots have no public URL (private disk) |
| Placeholders | never present in JSON-LD, sitemap, `llms.txt` or visible pages; the launch checklist lists them |
| Testimonials | unpublished or placeholder never rendered; publishing a placeholder is refused |
| Security | headers present; CSP has no `unsafe-inline` on public pages; guest → admin login redirect; non-admin user → 403; `.env.example` has no secrets |
| Cache | second request served from cache; saving a project clears it; `/contact` never cached; public responses set no cookies |
| Quality | lang key parity en ↔ ar; contrast unit test for tokens; RTL logical-property lint; query-count assertions on index pages |
| Browser (Pest 4 browser plugin, optional suite) | no horizontal overflow at 360 px in both locales, theme toggle persists, mobile menu keyboard flow, no JS console errors, and axe checks via `assertNoAccessibilityIssues()` |

---

## 13. Implementation plan (one commit, or a few small ones, per step)

| # | Step | Output |
|---|---|---|
| 0 | Bootstrap | Laravel 13 app, Pest, Pint, Larastan (level 6), CI (tests, pint, build, audits), `.env.example` without secrets, this `docs/` folder |
| 1 | Shell | `public` middleware group, locale routing + `/` redirect, layout, design tokens, fonts (subset + preload), theme toggle, header/footer/nav, 404 |
| 2 | Content model | migrations, models, factories, translatable casts, media collections + private disk + UUID naming |
| 3 | Settings | settings classes, placeholder detector, `Profile` public accessor |
| 4 | Admin | Filament panel (MFA, make-admin command), resources, settings pages, launch checklist, side-by-side translatable helper |
| 5 | Anonymity | `PublicProject` presenter, `AnonymityGuard`, media visibility sync job, tests |
| 6 | Seed content | EN + AR copy for 6 services, 6 projects + 3 drafts, facts, FAQs, technologies, categories, pages, placeholder testimonials, unpublished experience; media import from `seed-media` |
| 7 | Public pages | home, about, services index/detail, projects index/detail, privacy |
| 8 | Contact | Livewire form, anti-spam, mails (EN/AR, RTL), submissions resource, pruning |
| 9 | SEO | `Seo` object, JSON-LD graph, sitemap, robots, OG image generator |
| 10 | GEO | `llms.txt`, `llms-full.txt` |
| 11 | Hardening & performance | response cache, image pipeline (AVIF/WebP), security headers + CSP, cache invalidation, query audits |
| 12 | Admin screenshots (needs your OK first; see note) | run client apps locally with **fake data only**, capture admin screens into `seed-media` (private) |
| 13 | QA | Lighthouse mobile on the production build (all pages × 2 locales), axe, 360 px checks, fixes |
| 14 | Docs | `README.md` (setup, `.env` variables, deploy, adding a project), `docs/seo-checklist.md`, final report |

**Note on step 12:** running Petrogina, NARRVA, Cairo Key, Ludic and ArchPrep locally writes into their folders: `storage/logs`, framework caches, and a database file unless pointed elsewhere.
- **Databases:** I'll point `DB_DATABASE` at throwaway SQLite files in my scratch directory and seed only with factories or my own fake-data scripts. I will **never** run their own seeders (Ludic's contains real credentials) and will never touch their `.env` files.
- **ArchPrep:** synthetic meshes generated with trimesh, never the real fixtures.
- **Git state:** no git operations in those repos. Their working trees will show only gitignored runtime files.

---

## 14. Still open (non-blocking — defaults chosen)

1. **Hosting target**
   - Default: host-agnostic (MySQL 8, database queue, scheduler-driven worker fallback, GD-based OG images).
   - Telling me Laravel Cloud, Forge/VPS or shared hosting lets me tune the deploy docs and enable Redis/Horizon if available.
2. **Mail sender domain**
   - `MAIL_FROM_ADDRESS` should be e.g. `hello@omarkhaled.info` with SPF/DKIM/DMARC on the domain. Which SMTP provider?
3. **Accent colour**
   - Vermilion (#FF6A3D / #E4572E) is my recommendation for the print-heritage concept. Say so if you'd prefer something else.
4. **Arabic register**
   - Modern Standard Arabic, professional; the old portfolio used Egyptian colloquial.
5. **Hiding placeholders on visible pages**
   - This goes beyond what you asked (JSON-LD/sitemap/llms only). Keep it?
6. **Bio facts**
   - Years of experience, the "Senior" title, and employment dates are left out until you set them in settings and experience entries.
