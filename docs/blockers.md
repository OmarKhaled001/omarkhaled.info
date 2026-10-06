# Blockers & workarounds

Anything that could not be done exactly as specified, why, the workaround in place, and what would unblock it.

## B-001 · Arabic pages: lab LCP 1.81 s vs the 1.8 s target (5–10 ms over)
- **Status:** Lighthouse Performance is **99** on every Arabic page (the definition-of-done threshold is 95); English pages are at 1.50–1.65 s.
- **Why:** under Lighthouse's simulated slow-4G model, Arabic pages need three font files above the fold (IBM Plex Sans Arabic 400 + 600/700 for Arabic glyphs, Geist for Latin terms such as "Laravel" inside Arabic headings) versus one on English pages. Tried and measured: subsetting the Arabic fonts (−22 %), a single Plex file with Latin glyphs (bigger overall), preloading fewer faces, and `font-display: optional` — the simulated LCP stayed at ~1.805 s in every variant while some variants reintroduced layout shift.
- **Workaround in place:** the configuration with the best visual result and CLS 0 (`docs/lighthouse.md`).
- **What would close it:** serving fonts from a CDN edge close to visitors (real-world LCP will be lower than the lab model), or dropping the Latin face on Arabic pages (Latin words would render in the system font).

## B-002 · Step 12 (admin screenshots of client apps) — skipped by instruction
- No client application was run. Projects without approved public screenshots use the generated SVG plates; private captures of the three live public sites are stored but hidden.

## B-003 · Incident during setup (resolved)
- The preview launcher briefly started the **client-manager** dev server (seconds, no files changed — verified). See D-005. From then on only this repo's server was started.
