# Projects Inventory — omarkhaled.info

_Generated 2026-10-06 from a read-only scan of `/home/mora-khaled/projects`. Facts come from code, git history, and asset files; anything marked **(inferred)** is a reading, not something the files state._

---

## 0. What was scanned and what was excluded

| Folder | Verdict | Reason |
|---|---|---|
| `Petrogina /` (+ `new-petro/`, `Frontend.zip`) | ✅ **Flagship** | Live B2B export platform, deep Filament build, real tests |
| `archprep/` | ✅ Portfolio | Laravel + Python 3D-geometry microservice, heavy testing |
| `narrva/` (+ `narrva-frontend/`) | ✅ Portfolio | Full e-commerce + custom-design marketplace |
| `Cairokey/` | ✅ Portfolio | Live tourism booking platform + brand identity |
| `Ludic/` | ✅ Portfolio | 6-language corporate site + Filament CMS (2024) |
| `client-manager/` | ✅ Portfolio (anonymized) | Internal client-ops tool, security-focused (Next.js) |
| `printalia.png/.jpg` | ⚠️ Assets only | Live site mockup, **no source code in this folder** |
| `Army/` | ⚠️ Side project | Single-file Arabic countdown app |
| `omar-portfolio/` | 📄 Content source | Abandoned Next.js portfolio; bio, skills, experience copy harvested below |
| `profile/` | 📄 Empty | Untouched Laravel 13 skeleton + Laravel Boost (possible home for the new site) |
| `app/`, `d/`, `saas/`, `my-app/`, `task/`, `tenancy-app/`, `taskflow/` | ❌ Skeletons | Stock starter kits, zero custom domain code |
| `Templates/` | ❌ Third-party | Vristo / Vuexy commercial themes, cloned hospital system by another author |
| `olympus-authoring-*`, `shipd-agent/` | ❌ Not portfolio | Work tooling / third-party repo |

---

## 1. Project records

### 1.1 Petrogina Trading — B2B agricultural export platform ⭐ flagship

| Field | Value |
|---|---|
| **Pitch** | A bilingual export platform that connects international buyers with Egyptian fresh & frozen produce — catalogue, quotes, orders, and sea-freight tracking in one place, run from a Filament back-office with AI assistants. |
| **Client / industry** | Petrogina Trading (Egypt) — agri-food export / logistics |
| **Live** | https://petroginatrading.com |
| **Repo** | github.com/OmarKhaled001/petrogina (72 commits, 2026-04-20 → 2026-09-30) — public or private? (question) |
| **Problem** | Foreign buyers need trustworthy product specs, seasonality, and shipment visibility; the exporter needs one system for catalogue, quote requests, orders, shipments, recruiting, and SEO instead of email + spreadsheets. |
| **Solution** | Public bilingual site + authenticated customer portal + Filament admin with granular roles + two AI assistants (admin and customer) with server-enforced tool permissions. |
| **Stack** | Laravel 12, PHP 8.4, Filament 5, Livewire 4, Tailwind 4, Pest 4, spatie/activitylog, PhpSpreadsheet, Telescope, Google Gemini API; v2 front-end: GSAP + ScrollTrigger |
| **Key features** | • Product catalogue with spec sheets, seasons calendar, sourcing-regions map, "Farm to Port" 9-stage journey · • Excel catalogue import (`ProductImportService`) · • Customer portal: register/login, orders, quote requests, shipment tracking, notifications (separate DB connection for customers) · • Shipping: carriers with tracking URL templates (Maersk, MSC, CMA-CGM, Hapag-Lloyd, DHL), order/shipment/payment status enums + status-change notifications · • 20 Filament resources (Orders, Shipments, QuoteRequests, Products, Careers, JobApplications, SeoPages, Roles, …), dashboard widgets, settings · • AI assistants (Gemini) for admins and customers with per-role tool classes; documented threat model ("the model is not a security boundary") · • Careers module with job applications · • Per-page SEO manager, sitemap.xml, robots.txt, AR/EN · • Activity log, role policies throughout |
| **Engineering signals** | 27 test files (feature + unit): AI-assistant security, permissions, shipping, pricing, catalogue import, customer accounts, upload limits, error pages. Docs: `docs/ai-assistants.md`, `docs/deployment.md`, `docs/customer-accounts.md`. v2 static prototype (`new-petro/`) shipped with Playwright tests for bilingual layout & zero overflow at all widths. |
| **My role** | (inferred) Sole full-stack developer — design → build → deploy. **Confirm.** |
| **Results / metrics** | None found. **Needed.** |
| **Assets** | `Petrogina /Media/Petrogina.png` (1672×941 launch mockup — **shows the old v1 purple design**) · `Petrogina /Media/petrogina.jpg` (vertical) · `Petrogina /Media/Brand/BLUE PNG.png` (logo) · 3 screen-recordings (1920×1080): `Petrogina - Dashboard#1.mp4` (10 min), `Dashboard#2.mp4` (7 min), `Petrogina .mp4` (3.6 min) · v2 imagery: `new-petro/assets/images/hero-farm-to-port.webp` (AI-generated) |
| **Gaps** | Current (v2) screenshots of public site, portal, and admin; metrics; confirmation that logo/brand was or wasn't yours. |

### 1.2 ArchPrep — dental arch-scan preparation SaaS

| Field | Value |
|---|---|
| **Pitch** | A batch pipeline for dental labs that repairs upper/lower arch scans, detects bite interference, auto-separates arches, and lets technicians review in a 3D viewer before downloading files and a bilingual PDF report. |
| **Industry** | Dental labs / health-tech |
| **Live / repo** | None (local git, 14 commits, 2026-09-28 → 29, no remote) |
| **Problem** | Raw intraoral scans contain debris, holes, self-intersections, and overlapping arches; manual cleanup per case is slow and error-prone. |
| **Solution** | Laravel orchestrates batches via queued jobs; a Python FastAPI service (pymeshlab, trimesh, SciPy) does mesh repair and interference analysis; results stream live to a three.js review viewer. |
| **Architecture** | Laravel 13 + Livewire 4 → `Bus::batch` jobs on Redis/Horizon → HTTP JSON to FastAPI service (shared storage volume, path sandboxing, typed exceptions, selective retries) → events broadcast over Reverb → three.js viewer with heatmaps. Repository pattern + service layer + DTOs, policy per model. Dockerized Python service (multi-stage, non-root, healthcheck). |
| **Stack** | Laravel 13, Livewire 4, Fortify, Horizon, Reverb, Redis, three.js, Tailwind 4, Pest; Python: FastAPI, pymeshlab, trimesh, SciPy, NumPy, Docker; headless Chrome PDF |
| **Key features** | Batch upload with automatic upper/lower pairing · processing presets · real-time progress · 3D review with interference heatmaps, manual moves + undo · approval/reopen audit trail with authorship · repaired/adjusted downloads (signed URLs) · bilingual AR/EN PDF report with proper Arabic shaping · Arabic-first UI |
| **Engineering signals** | 197 Pest tests + 67 pytest + 4 JS tests; contract fixtures for the Python API; README documents measured sizing (e.g. 644 s / 3.09 GB peak at 1M faces). |
| **Results / metrics** | Benchmarks exist (above); business outcomes unknown. |
| **Assets** | **None.** No screenshots or logo. |
| **⚠️ Caution** | `tests/Fixtures/real/*.stl` are real patient scans (gitignored) — never publish. |
| **Gaps** | Is this a client project, a product of yours, or R&D? May it be shown publicly? Screenshots of batch list, 3D viewer, PDF report. |

### 1.3 NARRVA — storytelling apparel store + custom-design marketplace

| Field | Value |
|---|---|
| **Pitch** | A bilingual e-commerce platform for a story-driven apparel brand, where customers buy ready-made pieces or commission custom designs through designer chat — and earn commission when others buy their published ideas. |
| **Industry** | Fashion e-commerce (Egypt) |
| **Live / repo** | No live domain found. github.com/OmarKhaled001/narrva (3 commits pushed; **259 uncommitted changes** — most recent work isn't on GitHub yet) |
| **Problem** | A brand selling personal, story-based garments needs a full commerce stack plus a structured design-commission workflow and an idea marketplace with referral revenue. |
| **Solution** | Locale-prefixed Blade storefront + Paymob payments + design-request workflow (14 statuses, versioned previews, chat) + ideas marketplace with frozen commission splits + Filament back-office with per-module permissions and analytics. |
| **Stack** | Laravel 13, Filament 5, Alpine.js, GSAP, Lenis, Tailwind 4, Paymob (hand-written client, HMAC-SHA512 webhook verification), Socialite (Google, Facebook, Instagram), Pest |
| **Key features** | Cart/checkout with stock reservation + scheduled expiry · gift cards · design requests with chat, attachments, versions, approval · ideas marketplace + referral links + commission payouts · shipping zones by Egyptian governorate · order tracking with status history · versioned policy pages with recorded customer agreements · read-only JSON API v1 · 18 Filament resources, 13 widgets (sales, profit, visitors, top customers) · audit log · separate admin/customer guards |
| **Engineering signals** | 294 Pest test cases; 45 migrations / 35 models; 22 service classes; 25 policies. |
| **Design prototype** | `narrva-frontend/` — React 19 + TS bilingual UI prototype with Playwright + axe accessibility tests; 16 ready screenshots (EN/AR, desktop/mobile) in `narrva-frontend/artifacts/`. Present as part of this case study, not as a separate project. |
| **Assets** | `narrva-frontend/artifacts/*.png` (16 UI screenshots — of the prototype, not the Laravel build) · brand/product art in `narrva/public/images/` and `storage/app/public/products/` |
| **Gaps** | Is NARRVA a client, your own brand, or in progress? Launch status/URL. Screenshots of the real Laravel storefront + admin. Commit and push the pending work before linking the repo. |

### 1.4 Cairo Key — tourism booking platform + brand identity

| Field | Value |
|---|---|
| **Pitch** | A bilingual booking platform for a Cairo tourism company — furnished apartments, hotels, car rentals, and airport services — with a Filament back-office and a brand identity designed from scratch. |
| **Industry** | Travel / hospitality (Egypt) |
| **Live** | https://cairokey.net · instagram.com/cairokey2026 · tiktok.com/@cairokey2026 |
| **Repo** | github.com/OmarKhaled001/cairokey (62 commits, 2026-03-17 → 2026-09-04) |
| **Stack** | Laravel 12, PHP 8.4, Filament 5, Livewire 4, Tailwind 4, astrotomic/laravel-translatable, mcamara/laravel-localization, spatie settings & sluggable, Google OAuth |
| **Key features** | Listings + detail pages for apartments, hotels, cars, offers, services · keyword-mapped search · client accounts with Google sign-in and reservations · Filament resources (Apartments, Bookings, Cars, Clients + bookings relation, Hotels, Offers, Services), bookings chart + stats widgets · RTL/LTR, dark mode, reduced-motion support |
| **Design work** | Kufic-style Arabic wordmark logo (Cairo Tower + keyhole), logo presentation kit, business cards, Facebook banner, launch mockups — **(inferred) designed by you; confirm.** |
| **Assets** | `Cairokey/Media/cairokey-cover.png` (1672×941 launch mockup) · `cairokey.jpg` (vertical) · `Media/jpg/cairo tower-0١.jpg` (logo) · `Media/facebook-banner.jpg` · `Media/card/card-0١.png` · `App/public/assets/images/cover.png` |
| **Gaps** | Metrics (bookings, traffic); admin screenshots; confirm design authorship. |

### 1.5 Ludic — multilingual corporate site + CMS

| Field | Value |
|---|---|
| **Pitch** | A six-language corporate website (EN, AR, ES, IT, FR, DE) for a UAE trader of nuts, green coffee, and dried fruits, fully editable through a Filament CMS. |
| **Client** | Ludic L.L.C-FZ (UAE) |
| **Live** | Not found in code — ludicuae.com is **(inferred)** from the email domain. instagram.com/ludic.l.l.cfz |
| **Repo** | github.com/OmarKhaled001/Ludic (243 commits, 2024-10-25 → 2024-11-27) |
| **Stack** | Laravel 10, Filament 3.2, spatie media library, filament-google-maps, translatable fields, mcamara localization, Bootstrap (+RTL), Swiper |
| **Key features** | Pages, services, products, branches with Google Maps, clients, contact form · per-page SEO in all six languages · site-visit tracking middleware + stats widgets · coming-soon gate |
| **Assets** | `Ludic/Media/Ludic.png` (2752×1536 mockup) · `Ludic/Media/Ludic.mp4` (2:51 screen recording) |
| **⚠️ Caution** | `database/seeders/DatabaseSeeder.php` hard-codes an admin email + password — rotate if this was ever deployed with it, and never publish the repo as-is. |
| **Gaps** | Live URL, still online?, metrics. |

### 1.6 Client Control Panel — internal client-ops tool (anonymized case study)

| Field | Value |
|---|---|
| **Pitch** | A private operations panel for a freelance studio: every client's hosting details encrypted at rest, and every change request tracked from intake through testing to delivery. |
| **Live / repo** | Local only (built 2026-09-26) |
| **Stack** | Next.js 16 (App Router), React 19, TypeScript, Prisma + SQLite, iron-session, zod, Tailwind 4 |
| **Key features** | Dashboard KPIs (overdue 7+ days, completed-not-delivered, urgent) · cross-client "Pending Work" queue sorted by priority then age · 9-state request workflow with test + delivery records · AES-256-GCM credential encryption, admin-only reveal written to audit log · RBAC (Admin/Viewer) · login lockout · CSP/HSTS headers · typed-name confirmation on destructive deletes · Google-Sheets migration script |
| **Why include** | Shows security thinking and product sense outside Laravel. Lower priority than the Laravel projects. |
| **⚠️ Caution** | `prisma/import-google-sheet.ts` contains **plaintext SSH/email passwords for real clients** and `prisma/seed.ts` names real clients. Never publish; consider rotating those credentials and moving the import data out of source. |
| **Assets** | None. Screenshots would need seeded **fake** data. |

### 1.7 Projects referenced in the old portfolio — **no code found here**

From `omar-portfolio/lib/i18n.ts`:

| Project | Claimed description | Stack claimed | Status |
|---|---|---|---|
| **QRX** | "End-to-end QR Code Generator platform… multiple feature tiers, admin control panels, analytics, dynamic QR management." | Laravel, Filament, MySQL, Livewire | Need source / link / screenshots |
| **VMS — Resala Charity** | "Volunteer Management System… role-based access control, event coordination, member tracking across regions." | Laravel, Filament, Spatie, MySQL | Need source / link / screenshots / permission |
| **ERP & POS Systems** | "Sales, purchases, stock, suppliers, reporting." | Laravel, Filament, Repository Pattern, REST API | Generic group — need a concrete named project |

### 1.8 Printalia — assets only

`printalia.png` (3840×2160) / `printalia.jpg` mockups for **https://printalia.net**: an Arabic print-on-demand platform ("launch your own clothing brand from home") with subscriptions, FAQ, policies. Source code is not in this folder, so stack and role are unverified.

### 1.9 هتعدي يا دفعة (Army) — side project

Single-file Arabic RTL countdown for Egyptian conscripts (3-step mobile wizard, progress ring, localStorage). github.com/OmarKhaled001/het3dy-ya-daf3a. Could appear in a small "Side projects / lab" strip; not a case study.

---

## 2. Ranking for a global-client portfolio

Criteria: relevance to international buyers hiring a Laravel/Filament developer, technical depth, proof (live URL, tests), visual material available, and confidentiality risk.

| # | Project | Primary service it proves | Why it ranks here |
|---|---|---|---|
| 1 | **Petrogina Trading** | CRM & admin panels (Filament), e-commerce/B2B portals, integrations (AI) | Live, international-facing B2B, deepest Filament build, AI with a security model, real tests. Best flagship. |
| 2 | **NARRVA** | E-commerce, payments, complex workflows | Most complete commerce domain: payments, marketplace, commissions, 294 tests. Needs launch status + screenshots. |
| 3 | **ArchPrep** | SaaS, APIs & integrations, performance | Most impressive engineering (queues, websockets, 3D, Python microservice). Global labs relate. Blocked on permission + visuals. |
| 4 | **Cairo Key** | Booking platforms, design + development | Live, bilingual, plus branding — proves the "designer who codes" story. |
| 5 | **Ludic** | Corporate websites, multilingual CMS | Six languages is a strong signal for global clients; older stack (Laravel 10). |
| 6 | **QRX** *(pending info)* | SaaS with tiers | Could be the "SaaS / subscriptions" proof the site lacks — only if you can supply material. |
| 7 | **Resala VMS** *(pending info)* | Admin panels, RBAC | A recognizable non-profit; good social-impact angle. |
| 8 | **Client Control Panel** | Security, internal tools | Anonymized; shows range beyond Laravel. |

**Coverage check against the services you want to sell:**

| Service page | Proof projects | Gap? |
|---|---|---|
| Laravel development | Petrogina, NARRVA, Cairo Key, ArchPrep | — |
| SaaS / multi-tenant | ArchPrep (SaaS, **not multi-tenant**), QRX? | ⚠️ **No multi-tenant project found in code** (`tenancy-app` is a stub). Either supply one or I'll frame the page around SaaS architecture without claiming shipped multi-tenancy. |
| E-commerce | NARRVA, Petrogina (B2B), Printalia? | — |
| CRM & admin panels (Filament) | Petrogina, NARRVA, Cairo Key, Ludic, Resala VMS? | — |
| REST APIs & integrations | ArchPrep (Python service), NARRVA (Paymob, OAuth, API v1), Petrogina (Gemini, carrier tracking) | — |
| Performance / security audits | ArchPrep (benchmarks), Petrogina (AI security tests), Client Control Panel | ⚠️ No audit engagement as such; I'll present it as a capability backed by these practices unless you have a real audit to cite. |

---

## 3. Personal / bio data harvested (from `omar-portfolio/`)

- **Name:** Omar Khaled (metadata: "Omar Khaled Hussein") — عمر خالد
- **Headline used before:** "Full Stack Developer | Laravel & Filament Expert"
- **Story:** started as a professional graphic designer → now full-stack, specializing in Laravel + Filament ("From Pixels to Production").
- **Experience (no dates):** Schemacode — Full Stack Developer (EN says "Recent", AR says "currently") · hossam-x-studios — Web Developer · Media Print — Graphic Designer → Developer
- **Volunteering:** Resala Charity Association — leader & team manager since 2019
- **Claims to verify:** "5+ years development", "10 years of design", "40+ projects delivered", "Senior"
- **Skills:** PHP/Laravel, MySQL/Eloquent, REST APIs, OOP/MVC, Repository Pattern, SOLID · Livewire, Alpine.js, JavaScript, Tailwind, Bootstrap · Filament, complex admin panels, RBAC, multi-tenant systems, custom form builders, dashboard architecture
- **Links:** github.com/OmarKhaled001 · linkedin.com/in/omar-khaled-890b14396
- **No assets:** no profile photo, no testimonials, no education.

---

## 4. Security findings (act on these regardless of the portfolio)

1. `client-manager/prisma/import-google-sheet.ts` — plaintext SSH and email passwords for real clients, plus a Google Sheet URL. **Rotate those credentials** and remove them from source.
2. `Ludic/App/database/seeders/DatabaseSeeder.php` — hard-coded admin email + password. Rotate it on the live site if it was ever used there.
3. `Cairokey/App/.env`, `Petrogina /App/.env` — real env files present locally; confirm they're gitignored in their repos (I did not read their contents).
4. `archprep/tests/Fixtures/real/*.stl` — real patient scans; keep them out of any repo or demo.

Nothing from these files will be copied into the portfolio.

### 4.1 Git-history audit (2026-10-06, read-only — no repo was modified, nothing fetched or pushed)

Method: searched every local branch and remote-tracking ref with `git log --all -S<value>` and path filters; secret values were compared in-process and never printed. Remote-tracking refs reflect the last fetch, so GitHub may hold additional commits.

| Repo (GitHub visibility) | Finding | Pushed? |
|---|---|---|
| **petrogina** (`Petrogina /App`, PRIVATE) | 🔴 **`.env.example` contains live secrets**: `DB_PASSWORD`, `MAIL_PASSWORD`, `CUSTOMER_DB_PASSWORD`, `GEMINI_API_KEY`, and `APP_KEY` are byte-identical to the real `.env`. The same file also contains the **SSH password and email password** that appear in client-manager's import script. Introduced/last changed in commit `396b4da` (2026-09-24); `.env.example` also touched in `df0cc36`, `aef57af`, `8535e5a`. | **Yes** — on `main` and `origin/main`. Also present in a Kilo worktree copy (`.kilo/worktrees/sour-tendency/.env.example`). |
| **Ludic** (`Ludic/App`, PRIVATE) | 🟠 Hard-coded admin password in `database/seeders/DatabaseSeeder.php`, introduced in 1 commit. | **Yes** — present at the `origin/master` tip. |
| **cairokey** (`Cairokey/App`, PRIVATE) | 🟢 `.env` never committed; `.gitignore` covers it; none of the client-manager credential values found in history. | — |
| **narrva** (PRIVATE) | 🟢 `.env` never committed; no credential values found. | — |
| **archprep** (no remote) | 🟢 Real patient scans **never committed**. Only synthetic fixtures (`tests/Fixtures/scans/tetrahedron-ascii.stl`, `tetrahedron-binary.stl`) are in history. | — |
| **het3dy-ya-daf3a** (`Army`, PUBLIC) | 🟢 No credential values found. | — |
| **client-manager** | Not a git repository — the import script and `prisma/dev.db` were never committed anywhere. | — |

**Implications:** because Petrogina's `APP_KEY` leaked, anything encrypted with it (encrypted casts, encrypted cookies, signed URLs) should be considered exposed — rotate `APP_KEY` (use `APP_PREVIOUS_KEYS` for graceful rotation), the Gemini API key, both DB passwords, the mail password, and the SSH password. The repos are private, which limits exposure to collaborators and integrations with repo access (the `copilot/…` branch shows an AI agent had access). Rewriting history (git-filter-repo / BFG) is optional after rotation; rotation is what actually closes the hole.

### 4.2 Corrections from the live sites (captured 2026-10-06)

- **Petrogina's live site already runs the v2 navy/mint redesign** (not v1 as the old mockup suggested). Its `<title>` contains a typo: "Petrogina **Tradeing**".
- **Printalia** (printalia.net) is a print-on-demand platform **in Yemen** (design → list → they produce and deliver) with designer subscription tiers and a designer login.
- Screenshots captured: 7 pages each for Cairo Key and Petrogina, 2 for Printalia; desktop 1440×900 @2x, desktop full-page, and mobile 390×844 @3x → `storage/app/seed-media/screenshots/` (gitignored, with `manifest.json`).

---

## 5. Questions for you

**Identity & contact**
1. Contact email for the site and the form recipient (the prompt still has `{{MY_EMAIL}}`).
2. Upwork profile URL (and Behance/Dribbble if you want the design work linked).
3. Bio facts to state publicly: years in development, years in design, "Senior" or not, current employer (Schemacode — still current? dates?), number of projects delivered (is "40+" accurate?).
4. A professional photo (or should the design avoid one?).
5. Any client testimonials — even 2–3 short quotes with name, role, company. Otherwise I'll ship clearly-marked placeholders hidden until you fill them.

**Permission & confidentiality — for each of Petrogina, NARRVA, ArchPrep, Cairo Key, Ludic, Printalia:**
6. May I name the client, show screenshots, and link the live site?
7. Was it a client project, your own product, or an employer project (e.g. via Schemacode)? What was your exact role (solo? designed the brand too?)
8. Can the GitHub repo link be public, or should it stay private?

**Missing material**
9. **Metrics** for any project — users, orders, bookings, page speed, time saved, conversion, uptime. Even rough numbers ("cut quote turnaround from days to hours") make the case studies much stronger. Which can you share?
10. **QRX, Resala VMS, ERP/POS, Printalia** — where is the code or live URL? Do you want them in the portfolio? Do you have screenshots?
11. **Multi-tenant SaaS:** do you have a real shipped multi-tenant project? If not, OK to present the SaaS service without claiming one?
12. **ArchPrep:** client work, startup, or R&D? Showable at all?
13. **NARRVA:** launched? Your brand or a client's? Domain?
14. **Screenshots:** the current Petrogina v2 design, Cairo Key admin, and NARRVA's Laravel build have no screenshots. I can capture public pages from the live sites (cairokey.net, petroginatrading.com, printalia.net) in the browser; admin and portal screens need you to run the apps with demo data, or I can run them locally with seeded fake data. Which do you prefer?

**Technical decisions (affect the spec)**
15. **Laravel version:** you wrote "Laravel 12 (latest stable)", but **Laravel 13** is the current stable and your recent projects (ArchPrep, NARRVA, `profile/`) already use it. I recommend **Laravel 13 + Filament 5 + Livewire 4** (Filament 5 requires Livewire 4, not 3). OK?
16. **Where to build:** reuse the empty `profile/` skeleton (already Laravel 13 + Boost), or start a fresh repo `omarkhaled.info/`? This inventory currently sits at `/home/mora-khaled/projects/docs/` and will move into the site repo.
17. **Hosting target:** `profile/boost.json` has `cloud: true` — are you deploying to Laravel Cloud, a VPS (Forge/Ploi), or shared hosting? This drives queue, cache, and image-pipeline choices.
