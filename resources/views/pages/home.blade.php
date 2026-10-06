<x-layouts.site :seo="$seo">
    {{-- Hero: text-only so the LCP element is a heading, not an image. --}}
    <section class="relative overflow-hidden border-b border-border" aria-labelledby="hero-title">
        <div class="bg-blueprint pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="container-site relative grid gap-12 pt-16 pb-20 sm:pt-24 md:grid-cols-12 md:items-end lg:pb-28">
            <div class="md:col-span-8">
                <p class="slug flex items-center gap-2">
                    @if ($profile->availabilityStatus() === 'available' && $profile->availabilityNote())
                        <span class="relative inline-flex size-2" aria-hidden="true">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-accent opacity-60 motion-reduce:hidden"></span>
                            <span class="relative inline-flex size-2 rounded-full bg-accent"></span>
                        </span>
                        <span>{{ $profile->availabilityNote() }} · </span>
                    @endif
                    <span>{{ __('home.hero_slug') }}</span>
                </p>
                <h1 id="hero-title" class="mt-6 text-display font-semibold tracking-display text-balance">
                    {{ $profile->heroHeadline() ?? __('home.meta_title') }}
                </h1>
                @if ($profile->heroSubheadline())
                    <p class="mt-7 max-w-2xl text-lede text-muted text-pretty">{{ $profile->heroSubheadline() }}</p>
                @endif
                <div class="mt-10 flex flex-wrap gap-3">
                    <a href="{{ route('contact') }}" class="btn-primary">
                        {{ $profile->heroCtaText() }}
                        <x-lucide-arrow-right class="icon-dir size-4" aria-hidden="true" />
                    </a>
                    <a href="{{ route('projects.index') }}" class="btn-secondary">{{ __('site.cta.see_work') }}</a>
                </div>
            </div>

            {{-- "Job ticket": the print-shop docket, holding the facts a buyer scans first. --}}
            <aside class="md:col-span-4" aria-label="{{ __('home.spec.title') }}">
                <dl class="card relative divide-y divide-dashed divide-border font-mono text-[0.8rem]">
                    <div class="flex items-center justify-between px-5 py-3">
                        <dt class="slug">{{ __('home.spec.title') }}</dt>
                        <dd class="text-accent-text">OK/{{ now()->format('y') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 px-5 py-3"><dt class="text-muted">{{ __('home.spec.stack') }}</dt><dd class="text-end">{{ __('home.spec.stack_value') }}</dd></div>
                    <div class="flex justify-between gap-4 px-5 py-3"><dt class="text-muted">{{ __('home.spec.based') }}</dt><dd class="text-end">{{ $profile->location() }} · {{ $profile->utcOffsetLabel() }}</dd></div>
                    <div class="flex justify-between gap-4 px-5 py-3"><dt class="text-muted">{{ __('home.spec.languages') }}</dt><dd class="text-end">{{ __('home.spec.languages_value') }}</dd></div>
                    <div class="flex justify-between gap-4 px-5 py-3"><dt class="text-muted">{{ __('home.spec.replies') }}</dt><dd class="text-end">{{ __('home.spec.replies_value', ['hours' => $profile->responseTimeHours()]) }}</dd></div>
                </dl>
            </aside>
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
