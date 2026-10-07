<x-layouts.site :seo="$seo">
    <section class="relative overflow-hidden border-b border-border">
        <div class="bg-blueprint pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="container-site relative grid gap-12 py-16 sm:py-24 lg:grid-cols-12">
            <div class="lg:col-span-7">
                <x-site.breadcrumbs :items="[[__('pages.breadcrumb_home'), route('home')], [__('site.nav.about'), null]]" />
                <p class="slug mt-10">{{ __('about.slug') }}</p>
                <h1 class="mt-3 text-h1 font-semibold tracking-display text-balance">{{ $page->title }}</h1>
                <p class="mt-6 text-lede text-muted">{{ __('about.lede') }}</p>
                @if ($years = $profile->yearsExperience())
                    <p class="mt-3 font-mono text-sm text-accent-text">{{ __('about.years', ['years' => $years]) }}</p>
                @endif
            </div>
            <div class="prose-site lg:col-span-5 lg:pt-24">{!! \App\Support\Html::clean($page->body) !!}</div>
        </div>
    </section>

    {{-- Introduction beside the portrait photo from Admin → Identity (App\Support\Design\Portrait). --}}
    @if ($photo = \App\Support\Design\Portrait::image())
        <section class="container-site mt-24" aria-labelledby="hello-title">
            <div class="grid gap-10 md:grid-cols-12 md:items-center lg:gap-16">
                <div class="md:col-span-7">
                    <h2 id="hello-title" class="hello-title">
                        <span class="block">{{ __('about.hello.greeting') }}</span>
                        <span class="block">{{ __('about.hello.name', ['name' => \Illuminate\Support\Str::before($profile->name(), ' ')]) }}</span>
                    </h2>
                    <p class="hello-role mt-8">{{ $profile->jobTitle() }}</p>
                    <p class="mt-6 max-w-md leading-relaxed text-muted">{{ __('about.hello.body') }}</p>
                    <div class="mt-8 flex flex-wrap gap-3">
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
                    </div>
                </div>
                <figure class="md:col-span-5" data-reveal>
                    <picture>
                        @foreach ($photo['sources'] as $type => $srcset)
                            <source type="{{ $type }}" srcset="{{ $srcset }}" sizes="(min-width: 75rem) 28rem, (min-width: 48rem) 40vw, 92vw">
                        @endforeach
                        <img src="{{ $photo['src'] }}" width="{{ $photo['width'] }}" height="{{ $photo['height'] }}"
                            alt="{{ __('about.hello.portrait', ['name' => $profile->name()]) }}" loading="lazy" decoding="async"
                            class="h-auto w-full rounded-lg border border-border bg-surface object-cover shadow-[var(--shadow)]">
                    </picture>
                </figure>
            </div>
        </section>
    @endif

    <section class="container-site mt-24" aria-labelledby="design-title">
        <p class="slug"><span class="text-accent-text">01</span> — Design</p>
        <h2 id="design-title" class="mt-3 max-w-3xl text-h2 font-semibold tracking-display text-balance">{{ __('about.design.title') }}</h2>
        <ul class="mt-10 grid gap-5 md:grid-cols-3">
            @foreach (__('about.design.points') as [$title, $body])
                <li class="card p-6" data-reveal data-reveal-delay="{{ min($loop->index, 5) }}">
                    <h3 class="font-semibold">{{ $title }}</h3>
                    <p class="mt-2 text-[0.95rem] text-muted">{{ $body }}</p>
                </li>
            @endforeach
        </ul>
    </section>

    <section class="container-site mt-24" aria-labelledby="skills-title">
        <p class="slug"><span class="text-accent-text">02</span> — Skills</p>
        <h2 id="skills-title" class="mt-3 text-h2 font-semibold tracking-display">{{ __('about.skills') }}</h2>
        <dl class="mt-10 divide-y divide-border border-y border-border">
            @foreach (\App\Enums\TechnologyDomain::cases() as $domain)
                @if ($skills->has($domain->value))
                    <div class="grid gap-3 py-6 md:grid-cols-12">
                        <dt class="font-semibold md:col-span-3">{{ $domain->label() }}</dt>
                        <dd class="flex flex-wrap gap-2 md:col-span-9">
                            @foreach ($skills[$domain->value] as $tech)
                                <span class="chip">{{ $tech->name }}</span>
                            @endforeach
                        </dd>
                    </div>
                @endif
            @endforeach
        </dl>
    </section>

    <div class="container-site mt-24 grid gap-16 lg:grid-cols-12">
        <section class="lg:col-span-7" aria-labelledby="working-title">
            <p class="slug"><span class="text-accent-text">03</span> — Remote</p>
            <h2 id="working-title" class="mt-3 text-h2 font-semibold tracking-display text-balance">{{ __('about.working.title') }}</h2>
            <ul class="mt-8 space-y-6">
                @foreach (__('about.working.points') as [$title, $body])
                    <li class="flex gap-4">
                        <x-lucide-check class="mt-1 size-5 shrink-0 text-accent-text" aria-hidden="true" />
                        <div><h3 class="font-semibold">{{ $title }}</h3><p class="mt-1 text-muted">{{ $body }}</p></div>
                    </li>
                @endforeach
            </ul>
        </section>
        <section class="lg:col-span-5" aria-labelledby="tz-title">
            <div class="card p-7">
                <h2 id="tz-title" class="slug">{{ __('about.timezone.title') }}</h2>
                <p class="mt-4 font-mono text-3xl font-semibold tracking-tight">{{ $profile->utcOffsetLabel() }}</p>
                <p class="mt-4 text-muted">{{ __('about.timezone.body', ['location' => $profile->location(), 'offset' => $profile->utcOffsetLabel(), 'hours' => $profile->workingHours()]) }}</p>
                <p class="mt-3 text-muted">{{ __('about.timezone.reply', ['hours' => $profile->responseTimeHours()]) }}</p>
            </div>
        </section>
    </div>

    @if ($experience->isNotEmpty())
        <section class="container-site mt-24" aria-labelledby="experience-title">
            <p class="slug"><span class="text-accent-text">04</span> — CV</p>
            <h2 id="experience-title" class="mt-3 text-h2 font-semibold tracking-display">{{ __('about.experience') }}</h2>
            <ol class="mt-10 divide-y divide-border border-y border-border">
                @foreach ($experience as $item)
                    <li class="grid gap-2 py-6 md:grid-cols-12">
                        <p class="font-mono text-sm text-muted md:col-span-3">
                            {{ $item->started_on?->translatedFormat('M Y') }}@if ($item->started_on) – @endif{{ $item->is_current ? __('about.present') : $item->ended_on?->translatedFormat('M Y') }}
                        </p>
                        <div class="md:col-span-9">
                            <h3 class="font-semibold">{{ $item->role }} · {{ $item->company }}</h3>
                            @if ($item->description)<p class="mt-1 text-muted">{{ $item->description }}</p>@endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>
    @endif

    <x-site.cta-band />
</x-layouts.site>
