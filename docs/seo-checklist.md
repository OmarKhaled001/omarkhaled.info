# Post-launch SEO & GEO checklist

Work through this after the first production deploy ([docs/deployment.md](deployment.md) §6). Items are ordered by impact.

## Week 1 — get indexed

### Fill the identity gaps first
- [ ] Admin → **Contact & social**: real contact email, Upwork URL, WhatsApp (or empty). They are hidden everywhere until real — including `sameAs` in structured data.
- [ ] Admin → **Identity & hero**: years of experience (or leave empty — then no number is ever stated).
- [ ] Make the name, title and location identical on the site, LinkedIn, GitHub and Upwork ("Omar Khaled — Full-stack Laravel & Filament developer, Egypt"). Consistent entity data is what lets Google and AI assistants connect the profiles.

### Google Search Console
- [ ] Add a **Domain property** for `omarkhaled.info` (DNS TXT verification covers http/https/www). Or add a URL-prefix property and paste the code in Admin → **SEO → Google verification**.
- [ ] **Sitemaps** → submit `https://omarkhaled.info/sitemap.xml` (36 URLs: every page in English and Arabic with hreflang alternates).
- [ ] **URL Inspection** → request indexing for `/en`, `/ar`, `/en/services`, `/en/projects` and the two strongest case studies.
- [ ] After ~1 week: **Pages** report (no "Duplicate without canonical" — filtered project lists are intentionally `noindex`), **International targeting / hreflang** has no errors.
- [ ] After ~4 weeks: **Core Web Vitals** report (field data) — targets: LCP < 2.5 s, CLS < 0.1, INP < 200 ms (lab results are in [lighthouse.md](lighthouse.md)).

### Bing Webmaster Tools (also feeds ChatGPT search and Copilot)
- [ ] Sign in at <https://www.bing.com/webmasters> → **Import from Google Search Console** (fastest), or verify with the code in Admin → **SEO → Bing verification**.
- [ ] Submit the sitemap.
- [ ] Optional: enable **IndexNow** in Bing (new/updated pages get crawled within minutes).

### Validate the markup
- [ ] <https://search.google.com/test/rich-results> on the home page, a service page and a case study — expect Person, ProfessionalService, WebSite, BreadcrumbList, Service, FAQPage, CreativeWork/SoftwareApplication, no errors.
- [ ] <https://validator.schema.org> for the same URLs.
- [ ] Social previews: LinkedIn **Post Inspector**, Facebook **Sharing Debugger**, and an X/Twitter card preview for `/en` and one case study (OG images are 1200×630, generated per page and language).

## Week 2 — profiles and backlinks

### Google Business Profile (service-area business)
- [ ] Create a profile as a **service-area business** (hide the street address), name "Omar Khaled — Laravel Developer" or your registered business name, primary category **Software company**, secondary **Website designer**.
- [ ] Service area: your city/governorate **plus** "Online"; add the 6 services with short descriptions; link to `https://omarkhaled.info/en`.
- [ ] Ask your first clients for Google reviews once they're happy to be named.

### Links from your own profiles (high trust, easy wins)
- [ ] **GitHub**: website field → `https://omarkhaled.info`; create a profile README (`OmarKhaled001/OmarKhaled001`) with a one-line pitch and a link; pin public repositories.
- [ ] **LinkedIn**: website in contact info, **Featured** section with the site and 2–3 case studies, headline matching the site.
- [ ] **Upwork**: profile overview linking the site; add portfolio items that link to the matching case studies (anonymized ones are fine).
- [ ] **Behance** (optional, for the brand-identity work): upload the identity projects you're allowed to show, linking back.

### Further backlinks (pick 2–3 per month)
- [ ] Developer directories and marketplaces: Clutch, GoodFirms, Toptal/Arc profiles, Laravel-focused job boards.
- [ ] Write about real problems you solved — e.g. "AI assistants in Laravel: the model is not a security boundary", "Paymob webhooks done right", "Arabic RTL with Tailwind logical properties" — on dev.to / Medium / LinkedIn articles, canonical-linking to case studies.
- [ ] Contribute a Filament plugin or a fix to an open-source Laravel package; the GitHub profile then links back to the site.

## Ongoing — GEO (AI search: ChatGPT, Claude, Perplexity, Google AI Overviews)
- [ ] Check `https://omarkhaled.info/llms.txt` and `/llms-full.txt` after every content change (they're generated from the same data as the pages).
- [ ] `robots.txt` already allows GPTBot, OAI-SearchBot, ChatGPT-User, ClaudeBot, Claude-SearchBot, Claude-User, PerplexityBot, Perplexity-User, Google-Extended, Applebot-Extended — keep it that way.
- [ ] Monthly, ask ChatGPT (search on), Perplexity and Google: *"Laravel Filament developer Egypt"*, *"hire Laravel developer for SaaS"*, *"Filament admin panel developer"* — note whether and how the site is cited.
- [ ] Keep FAQ answers direct (first sentence answers the question), add new questions you actually get from clients.

## Ongoing — content
- [ ] When a client approves being named: Admin → Projects → **Visibility** → reveal name/link/logo/screenshots. URLs stay the same (neutral slugs), so rankings carry over.
- [ ] Add **verified results** to case studies (Admin → Projects → Results): numbers clients agree to publish (orders, time saved, uptime, page speed).
- [ ] Publish real testimonials (Admin → Testimonials: turn off *Placeholder*, then *Published*).
- [ ] Add each new project the same week it launches — fresh, specific pages are the strongest ranking signal a portfolio has.
- [ ] Quarterly: re-run Lighthouse (see [lighthouse.md](lighthouse.md)), review Search Console queries, and expand the pages that already get impressions.
