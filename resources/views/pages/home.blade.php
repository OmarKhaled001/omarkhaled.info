<x-layouts.site :seo="$seo">
    {{--
        Hero: short headline (the LCP element) beside a WebGL "platform stack" (resources/js/hero-scene.js).
        On tall-enough desktop screens the hero pins while scrolling pulls the four layers apart.
    --}}
    <section class="relative border-b border-border pin:h-[175svh]" data-hero aria-labelledby="hero-title">
        <div class="relative overflow-hidden pin:sticky pin:top-16 pin:h-[calc(100svh-4rem)]" data-hero-pin>
            <div class="bg-blueprint pointer-events-none absolute inset-0" aria-hidden="true"></div>
            <div class="container-site relative grid items-center gap-8 pt-14 pb-16 sm:pt-20 md:grid-cols-12 md:gap-6 pin:h-full pin:py-10">
                <div class="md:col-span-7 lg:col-span-6">
                    <p class="slug flex flex-wrap items-center gap-x-2 gap-y-1">
                        @if ($profile->availabilityStatus() === 'available' && $profile->availabilityNote())
                            <span class="flex items-center gap-2 text-accent-text">
                                <span class="relative inline-flex size-2" aria-hidden="true">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-accent opacity-60 motion-reduce:hidden"></span>
                                    <span class="relative inline-flex size-2 rounded-full bg-accent"></span>
                                </span>
                                {{ $profile->availabilityNote() }}
                            </span>
                            <span aria-hidden="true">·</span>
                        @endif
                        <span>{{ __('home.hero_slug') }}</span>
                    </p>
                    <h1 id="hero-title" class="mt-6 text-display font-semibold tracking-display text-balance">
                        {{ $profile->heroHeadlineHtml() ?? __('home.meta_title') }}
                    </h1>
                    @if ($profile->heroSubheadline())
                        <p class="mt-6 max-w-xl text-lede text-muted text-pretty">{{ $profile->heroSubheadline() }}</p>
                    @endif
                    <div class="mt-9 flex flex-wrap gap-3">
                        <a href="{{ route('contact') }}" class="btn-primary">
                            {{ $profile->heroCtaText() }}
                            <x-lucide-arrow-right class="icon-dir size-4" aria-hidden="true" />
                        </a>
                        @if ($cvUrl = $profile->cvUrl())
                            <a href="{{ $cvUrl }}" class="btn-secondary" download>
                                <x-lucide-download class="size-4" aria-hidden="true" />
                                {{ __('site.cta.download_cv') }}
                            </a>
                        @endif
                        <a href="{{ route('projects.index') }}" @class(['btn-ghost' => $cvUrl, 'btn-secondary' => ! $cvUrl])>{{ __('site.cta.see_work') }}</a>
                    </div>
                    @if ($rolesNote = $profile->rolesNote())
                        <p class="mt-5 flex items-center gap-2 text-[0.9rem] text-muted">
                            <x-lucide-briefcase class="size-4 shrink-0 text-accent-text" aria-hidden="true" />
                            {{ $rolesNote }}
                        </p>
                    @endif

                    <dl class="mt-10 grid max-w-xl grid-cols-2 gap-x-6 gap-y-4 border-t border-dashed border-border pt-6 text-[0.85rem] sm:grid-cols-3" aria-label="{{ __('home.spec.title') }}">
                        <div><dt class="slug">{{ __('home.spec.stack') }}</dt><dd class="mt-1 font-medium">{{ __('home.spec.stack_value') }}</dd></div>
                        <div><dt class="slug">{{ __('home.spec.based') }}</dt><dd class="mt-1 font-medium">{{ $profile->location() }} · {{ $profile->utcOffsetLabel() }}</dd></div>
                        <div><dt class="slug">{{ __('home.spec.replies') }}</dt><dd class="mt-1 font-medium">{{ __('home.spec.replies_value', ['hours' => $profile->responseTimeHours()]) }}</dd></div>
                    </dl>
                </div>

                {{-- Decorative 3D model; labels are positioned by the scene script from projected plate corners. --}}
                <figure class="hero-scene md:col-span-5 lg:col-span-6" data-hero-scene role="img" aria-label="{{ __('home.scene.label') }}">
                    <div class="hero-stage relative mx-auto aspect-square w-full max-w-[36rem] md:aspect-auto md:h-[clamp(20rem,calc(100svh-10rem),36rem)]">
                        <div class="hero-glow pointer-events-none absolute inset-[14%] rounded-full" aria-hidden="true"></div>
                        <canvas class="absolute inset-0 size-full" aria-hidden="true"></canvas>
                        {{-- CSS-only stack, shown when WebGL is missing or software-rendered. --}}
                        <div class="hero-css" aria-hidden="true">
                            @foreach (range(0, 3) as $layer)
                                <div class="hero-css-plate" data-layer="{{ $layer }}"></div>
                            @endforeach
                        </div>
                        <ol class="hero-labels" aria-hidden="true">
                            @foreach (__('home.scene.layers') as $i => [$name, $detail])
                                <li class="hero-label" data-layer="{{ 3 - $i }}">
                                    <span class="block font-medium">{{ $name }}</span>
                                    <span class="hero-label-detail">{{ $detail }}</span>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </figure>
            </div>

            <div class="pointer-events-none absolute inset-x-0 bottom-5 hidden justify-center pin:flex" data-hero-cue aria-hidden="true">
                <span class="slug flex items-center gap-2">{{ __('home.scene.scroll') }} <x-lucide-arrow-down class="size-3.5 motion-safe:animate-bounce" /></span>
            </div>
        </div>
    </section>

    {{-- Stack --}}
    @if ($stack->isNotEmpty())
        <section class="border-b border-border" aria-labelledby="stack-title">
            <div class="container-site flex flex-col gap-5 py-8 md:flex-row md:items-center md:gap-10">
                <h2 id="stack-title" class="slug shrink-0">{{ __('home.stack.title') }}</h2>
                <ul class="flex flex-wrap gap-x-6 gap-y-2 text-[0.95rem] font-medium text-muted">
                    @foreach ($stack as $tech)
                        <li class="flex items-center gap-2"><span class="size-1.5 rounded-full bg-border-strong" aria-hidden="true"></span>{{ $tech->name }}</li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- Two audiences: clients with a project, companies hiring. --}}
    @if ($profile->openToRoles())
        <section class="container-site mt-24 sm:mt-28" aria-labelledby="audiences-title">
            <x-site.section-header :slug="__('home.audiences.slug')" :title="__('home.audiences.title')" :lede="__('home.audiences.lede')" id="audiences-title" />
            <div class="mt-12 grid gap-5 md:grid-cols-2">
                @foreach (['clients', 'employers'] as $audience)
                    <article class="card relative flex flex-col p-7 sm:p-8" aria-labelledby="audience-{{ $audience }}" data-reveal data-reveal-delay="{{ $loop->index }}">
                        <span class="absolute -top-px start-7 h-px w-14 bg-accent" aria-hidden="true"></span>
                        <p class="slug flex items-center gap-2">
                            <x-dynamic-component :component="$audience === 'clients' ? 'lucide-rocket' : 'lucide-briefcase'" class="size-4 text-accent-text" aria-hidden="true" />
                            {{ __("home.audiences.{$audience}.eyebrow") }}
                        </p>
                        <h3 id="audience-{{ $audience }}" class="mt-4 text-h3 font-semibold tracking-tight">{{ __("home.audiences.{$audience}.title") }}</h3>
                        <p class="mt-3 text-muted">{{ __("home.audiences.{$audience}.body") }}</p>
                        <ul class="mt-6 space-y-2.5 text-[0.95rem]">
                            @foreach (__("home.audiences.{$audience}.points", ['location' => $profile->location(), 'offset' => $profile->utcOffsetLabel()]) as $point)
                                <li class="flex gap-3"><x-lucide-check class="mt-1 size-4 shrink-0 text-accent-text" aria-hidden="true" />{{ $point }}</li>
                            @endforeach
                        </ul>
                        <div class="mt-auto flex flex-wrap gap-3 pt-8">
                            @if ($audience === 'clients')
                                <a href="{{ route('contact') }}" class="btn-primary">
                                    {{ $profile->heroCtaText() }}
                                    <x-lucide-arrow-right class="icon-dir size-4" aria-hidden="true" />
                                </a>
                                <a href="{{ route('services.index') }}" class="btn-ghost">{{ __('site.cta.all_services') }}</a>
                            @else
                                @if ($cvUrl)
                                    <a href="{{ $cvUrl }}" class="btn-primary" download>
                                        <x-lucide-download class="size-4" aria-hidden="true" />
                                        {{ __('site.cta.download_cv') }}
                                    </a>
                                @endif
                                <a href="{{ route('contact', ['type' => config('portfolio.contact.role_type')]) }}" @class(['btn-secondary' => $cvUrl, 'btn-primary' => ! $cvUrl])>
                                    {{ __('site.cta.discuss_role') }}
                                    <x-lucide-arrow-right class="icon-dir size-4" aria-hidden="true" />
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Services --}}
    <section class="container-site mt-24 sm:mt-28" aria-labelledby="services-title">
        <div class="flex flex-col justify-between gap-6 md:flex-row md:items-end">
            <x-site.section-header :index="1" :slug="__('home.services.slug')" :title="__('home.services.title')" :lede="__('home.services.lede')" id="services-title" />
            <a href="{{ route('services.index') }}" class="link-arrow shrink-0">{{ __('site.cta.all_services') }} <x-lucide-arrow-right class="icon-dir size-4" aria-hidden="true" /></a>
        </div>
        <ul class="mt-12 grid gap-px overflow-hidden rounded-lg border border-border bg-border sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($services as $service)
                <li class="group relative bg-surface p-6 transition-colors hover:bg-surface-2 sm:p-7" data-reveal data-reveal-delay="{{ min($loop->index, 5) }}">
                    <span class="inline-flex size-10 items-center justify-center rounded-md border border-border bg-bg text-accent-text" aria-hidden="true">
                        <x-dynamic-component :component="'lucide-'.($service->icon ?: 'code')" class="size-5" />
                    </span>
                    <h3 class="mt-5 text-lg font-semibold tracking-tight">
                        <a href="{{ route('services.show', $service->slug) }}" class="after:absolute after:inset-0">{{ $service->title }}</a>
                    </h3>
                    <p class="mt-2 text-[0.95rem] text-muted">{{ $service->card_summary }}</p>
                    <x-lucide-arrow-up-right class="icon-dir absolute end-6 top-6 size-4 text-muted opacity-0 transition-opacity group-hover:opacity-100" aria-hidden="true" />
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Featured work: bento --}}
    @if ($projects->isNotEmpty())
        <section class="container-site mt-28" aria-labelledby="work-title">
            <div class="flex flex-col justify-between gap-6 md:flex-row md:items-end">
                <x-site.section-header :index="2" :slug="__('home.work.slug')" :title="__('home.work.title')" :lede="__('home.work.lede')" id="work-title" />
                <a href="{{ route('projects.index') }}" class="link-arrow shrink-0">{{ __('site.cta.all_projects') }} <x-lucide-arrow-right class="icon-dir size-4" aria-hidden="true" /></a>
            </div>
            <div class="mt-12 grid gap-5 md:grid-cols-6">
                @foreach ($projects as $project)
                    <x-site.project-card :project="$project" :index="$loop->iteration" :feature="$loop->first"
                        data-reveal data-reveal-delay="{{ min($loop->index, 5) }}"
                        :class="\Illuminate\Support\Arr::toCssClasses([
                            'md:col-span-4 md:row-span-2' => $loop->index === 0,
                            'md:col-span-2' => in_array($loop->index, [1, 2], true),
                            'md:col-span-3' => $loop->index >= 3,
                        ])" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Process --}}
    <section class="container-site mt-28" aria-labelledby="process-title">
        <x-site.section-header :index="3" :slug="__('home.process.slug')" :title="__('home.process.title')" :lede="__('home.process.lede')" id="process-title" />
        <ol class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (__('home.process.steps') as $i => [$title, $body])
                <li class="relative border-t border-border-strong pt-6" data-reveal data-reveal-delay="{{ min($i, 5) }}">
                    <span class="absolute -top-px start-0 h-px w-12 bg-accent" aria-hidden="true"></span>
                    <span class="font-mono text-sm text-accent-text">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                    <h3 class="mt-3 text-lg font-semibold">{{ $title }}</h3>
                    <p class="mt-2 text-[0.95rem] text-muted">{{ $body }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- Testimonials: hidden entirely until a real one is published. --}}
    @if ($testimonials->isNotEmpty())
        <section class="container-site mt-28" aria-labelledby="testimonials-title">
            <x-site.section-header :index="4" :slug="__('home.testimonials.slug')" :title="__('home.testimonials.title')" id="testimonials-title" />
            <div class="mt-12 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($testimonials as $testimonial)
                    <figure class="card flex flex-col p-7">
                        <blockquote class="text-[1.05rem] leading-relaxed">“{{ $testimonial->quote }}”</blockquote>
                        <figcaption class="mt-auto pt-6 text-sm">
                            <span class="font-semibold">{{ $testimonial->author_name }}</span>
                            <span class="block text-muted">{{ collect([$testimonial->author_role, $testimonial->company])->filter()->implode(', ') }}</span>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </section>
    @endif

    {{-- FAQ --}}
    @if ($faqs->isNotEmpty())
        <section class="container-site mt-28 grid gap-10 md:grid-cols-12" aria-labelledby="faq-title">
            <x-site.section-header class="md:col-span-4" :index="$testimonials->isNotEmpty() ? 5 : 4" :slug="__('home.faq.slug')" :title="__('home.faq.title')" id="faq-title" />
            <x-site.faq-list class="md:col-span-8" :faqs="$faqs" />
        </section>
    @endif

    <x-site.cta-band />
</x-layouts.site>
