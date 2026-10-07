# Lighthouse & QA results

_Measured 2026-10-07 with Lighthouse 12.8.2, mobile form factor, default simulated throttling (slow 4G, 4× CPU), headless Chromium._
_Setup: `APP_DEBUG=false`, full-page cache warm, assets built with `npm run build`, behind a reverse proxy doing Brotli/gzip and long-lived caching for `/build/*` — the same things nginx/Caddy do in production (see `docs/deployment.md`)._

_Home rows re-measured after the 3D hero, CV and logo changes (D-037–D-039). Headless Chromium renders WebGL in software, so audits see the CSS-3D fallback; GPU devices run WebGL lazily after load (see D-037). axe: 0 violations, no overflow at 360 px._

| Page | Performance | Accessibility | Best practices | SEO | LCP | CLS | TBT | FCP |
|---|---|---|---|---|---|---|---|---|
| `/en` (home, 3D hero) | **100** | **100** | **100** | **100** | 1.7 s | 0 | 0 ms | 1.4 s |
| `/ar` (home, 3D hero) | **100** | **100** | **100** | **100** | 1.7 s | 0 | 0 ms | 1.1 s |
| `/en/services/filament-admin-panels` | **100** | **100** | **100** | **100** | 1.65 s | 0 | 0 ms | 1.4 s |
| `/ar/services/filament-admin-panels` | **99** | **100** | **100** | **100** | 1.81 s | 0 | 0 ms | 1.1 s |
| `/en/projects/b2b-export-platform` | **100** | **100** | **100** | **100** | 1.50 s | 0 | 0 ms | 1.4 s |
| `/ar/projects/b2b-export-platform` | **99** | **100** | **100** | **100** | 1.81 s | 0 | 0 ms | 1.1 s |

All pages meet the definition of done (≥ 95 in every category). INP cannot be measured in the lab; TBT is 0 ms everywhere and public pages ship ~0.7 KB of JavaScript, so interaction latency is negligible.

## What moved the numbers
1. **Inlined CSS** (6 KB brotli) — removes the only render-blocking request; allowed by CSP hash.
2. **Font loading** — metric-matched fallbacks (`size-adjust`/`ascent-override` measured with fontTools), preloads of exactly the faces used above the fold, Geist Mono as `font-display: optional`, the IBM Plex Sans Arabic files subset to the Arabic blocks (−22 %). CLS went from 0.16–0.19 on Arabic pages to 0.
3. **Text-only hero** — the LCP element is the `<h1>`, never an image.
4. **Full-page response cache** — TTFB of a cached page is a file read.

## Other QA (automated, Playwright + axe-core 4)
- **axe-core WCAG 2.0/2.1/2.2 A + AA:** 0 violations on 40 variants (10 pages × EN/AR × light/dark).
- **360 px viewport:** no horizontal overflow on any of those 40 variants.
- **Keyboard:** first Tab focuses "Skip to content"; Enter moves focus to `<main>`. Mobile menu opens as a modal dialog, Escape closes it and returns focus to the button.
- **CSP:** no violations in the console on public pages, the admin login, or a real contact-form submission.
- **Pest:** see `composer test` (feature + unit + rendered-page leak check).
