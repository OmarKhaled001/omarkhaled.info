@php
    $filterUrl = fn (?string $type, ?string $tech) => route('projects.index', array_filter(['type' => $type, 'tech' => $tech]));
    $chip = 'inline-flex min-h-10 items-center rounded-full border px-4 text-[0.92rem] transition-colors';
@endphp
<x-layouts.site :seo="$seo">
    <section class="relative overflow-hidden border-b border-border">
        <div class="bg-blueprint pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="container-site relative py-16 sm:py-24">
            <x-site.breadcrumbs :items="[[__('pages.breadcrumb_home'), route('home')], [__('projects.breadcrumb'), null]]" />
            <p class="slug mt-10">{{ __('projects.slug') }}</p>
            <h1 class="mt-3 text-h1 font-semibold tracking-display text-balance">{{ __('projects.title') }}</h1>
            <p class="mt-6 max-w-2xl text-lede text-muted">{{ __('projects.lede') }}</p>
        </div>
    </section>

    <div class="container-site mt-12">
        {{-- Server-rendered filters: plain links, so crawlers and no-JS visitors see every project. --}}
        <nav aria-label="{{ __('projects.filter.label') }}" class="space-y-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="slug me-2 w-24 shrink-0">{{ __('projects.filter.type') }}</span>
                <a href="{{ $filterUrl(null, $activeTech) }}" @if (! $activeType) aria-current="true" @endif
                    @class([$chip, 'border-ink bg-ink text-bg' => ! $activeType, 'border-border bg-surface text-muted hover:text-ink' => $activeType])>{{ __('projects.filter.all') }}</a>
                @foreach ($categories as $category)
                    @php($active = $activeType === $category->slug)
                    <a href="{{ $filterUrl($category->slug, $activeTech) }}" @if ($active) aria-current="true" @endif
                        @class([$chip, 'border-ink bg-ink text-bg' => $active, 'border-border bg-surface text-muted hover:text-ink' => ! $active])>{{ $category->name }}</a>
                @endforeach
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="slug me-2 w-24 shrink-0">{{ __('projects.filter.tech') }}</span>
                <a href="{{ $filterUrl($activeType, null) }}" @if (! $activeTech) aria-current="true" @endif
                    @class([$chip, 'border-ink bg-ink text-bg' => ! $activeTech, 'border-border bg-surface text-muted hover:text-ink' => $activeTech])>{{ __('projects.filter.all') }}</a>
                @foreach ($technologies as $tech)
                    @php($active = $activeTech === $tech->slug)
                    <a href="{{ $filterUrl($activeType, $tech->slug) }}" @if ($active) aria-current="true" @endif
                        @class([$chip, 'border-ink bg-ink text-bg' => $active, 'border-border bg-surface text-muted hover:text-ink' => ! $active])>{{ $tech->name }}</a>
                @endforeach
            </div>
        </nav>

        <div class="mt-10 flex items-center justify-between gap-4 border-b border-border pb-4">
            <p class="font-mono text-sm text-muted" aria-live="polite">{{ trans_choice('projects.filter.count', $projects->count(), ['count' => $projects->count()]) }}</p>
            @if ($activeType || $activeTech)
                <a href="{{ route('projects.index') }}" class="link-arrow text-sm">{{ __('projects.filter.clear') }}</a>
            @endif
        </div>

        @if ($projects->isEmpty())
            <p class="py-24 text-center text-muted">{{ __('projects.filter.empty') }}</p>
        @else
            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($projects as $project)
                    <x-site.project-card :project="$project" :index="$loop->iteration" headingLevel="h2" data-reveal data-reveal-delay="{{ min($loop->index % 3, 5) }}" />
                @endforeach
            </div>
        @endif
    </div>

    <x-site.cta-band />
</x-layouts.site>
