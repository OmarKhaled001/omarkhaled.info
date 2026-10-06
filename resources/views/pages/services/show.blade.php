<x-layouts.site :seo="$seo">
    <section class="relative overflow-hidden border-b border-border">
        <div class="bg-blueprint pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="container-site relative py-16 sm:py-24">
            <x-site.breadcrumbs :items="[[__('pages.breadcrumb_home'), route('home')], [__('services.breadcrumb'), route('services.index')], [$service->title, null]]" />
            <p class="slug mt-10 flex items-center gap-2">
                <x-dynamic-component :component="'lucide-'.($service->icon ?: 'code')" class="size-4 text-accent-text" aria-hidden="true" />
                {{ $service->title }}
            </p>
            <h1 class="mt-4 max-w-4xl text-h1 font-semibold tracking-display text-balance">{{ $service->headline }}</h1>
            <p class="mt-6 max-w-3xl text-lede text-muted text-pretty">{{ $service->intro }}</p>
            <div class="mt-10 flex flex-wrap gap-3">
                <a href="{{ route('contact') }}" class="btn-primary">{{ app(\App\Support\Profile::class)->heroCtaText() }} <x-lucide-arrow-right class="icon-dir size-4" aria-hidden="true" /></a>
                @if ($projects->isNotEmpty())
                    <a href="#related" class="btn-secondary">{{ __('services.related') }}</a>
                @endif
            </div>
        </div>
    </section>

    <div class="container-site mt-20 grid gap-16 lg:grid-cols-12">
        @if ($service->problem)
            <section class="lg:col-span-5" aria-labelledby="problem-title">
                <p class="slug"><span class="text-accent-text">01</span> — {{ __('services.problem') }}</p>
                <h2 id="problem-title" class="sr-only">{{ __('services.problem') }}</h2>
                <div class="prose-site mt-5">{!! \App\Support\Html::clean($service->problem) !!}</div>
            </section>
        @endif

        <section class="lg:col-span-7" aria-labelledby="deliverables-title">
            <p class="slug"><span class="text-accent-text">02</span> — {{ __('services.deliverables') }}</p>
            <h2 id="deliverables-title" class="sr-only">{{ __('services.deliverables') }}</h2>
            <ul class="mt-5 divide-y divide-border border-y border-border">
                @foreach ($service->deliverables as $item)
                    <li class="grid gap-1 py-5 sm:grid-cols-[2rem_1fr]">
                        <x-lucide-check class="mt-1 size-5 text-accent-text" aria-hidden="true" />
                        <div>
                            <h3 class="font-semibold">{{ $item->title }}</h3>
                            @if ($item->body)<p class="mt-1 text-muted">{{ $item->body }}</p>@endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>

    <section class="container-site mt-24" aria-labelledby="process-title">
        <p class="slug"><span class="text-accent-text">03</span> — {{ __('services.process') }}</p>
        <h2 id="process-title" class="mt-3 text-h2 font-semibold tracking-display">{{ __('services.process') }}</h2>
        <ol class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($service->processSteps as $step)
                <li class="relative border-t border-border-strong pt-6" data-reveal style="--reveal-delay: {{ $loop->index }}">
                    <span class="absolute -top-px start-0 h-px w-12 bg-accent" aria-hidden="true"></span>
                    <span class="font-mono text-sm text-accent-text">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <h3 class="mt-3 text-lg font-semibold">{{ $step->title }}</h3>
                    @if ($step->body)<p class="mt-2 text-[0.95rem] text-muted">{{ $step->body }}</p>@endif
                </li>
            @endforeach
        </ol>
    </section>

    @if ($projects->isNotEmpty())
        <section id="related" class="container-site mt-24" aria-labelledby="related-title">
            <p class="slug"><span class="text-accent-text">04</span> — {{ __('services.related') }}</p>
            <h2 id="related-title" class="mt-3 text-h2 font-semibold tracking-display">{{ __('services.related') }}</h2>
            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($projects as $project)
                    <x-site.project-card :project="$project" :index="$loop->iteration" />
                @endforeach
            </div>
        </section>
    @endif

    @if ($service->faqs->isNotEmpty())
        <section class="container-site mt-24 grid gap-10 md:grid-cols-12" aria-labelledby="faq-title">
            <div class="md:col-span-4">
                <p class="slug"><span class="text-accent-text">05</span> — FAQ</p>
                <h2 id="faq-title" class="mt-3 text-h2 font-semibold tracking-display text-balance">{{ __('services.faq', ['service' => $service->title]) }}</h2>
            </div>
            <x-site.faq-list class="md:col-span-8" :faqs="$service->faqs" />
        </section>
    @endif

    @if ($others->isNotEmpty())
        <nav class="container-site mt-24" aria-labelledby="others-title">
            <h2 id="others-title" class="slug">{{ __('services.other') }}</h2>
            <ul class="mt-5 flex flex-wrap gap-2">
                @foreach ($others as $other)
                    <li><a href="{{ route('services.show', $other->slug) }}" class="chip min-h-10 px-4 text-[0.95rem] text-ink hover:border-border-strong">{{ $other->title }}</a></li>
                @endforeach
            </ul>
        </nav>
    @endif

    <x-site.cta-band />
</x-layouts.site>
