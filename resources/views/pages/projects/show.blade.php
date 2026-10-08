@php
    /** @var \App\Presenters\PublicProject $project */
    // Results get their own block after the features; every other section is rendered in model order.
    $sections = collect(\App\Models\Project::SECTIONS)->reject(fn ($s) => $s === 'results')
        ->mapWithKeys(fn ($s) => [$s => $project->section($s)])->filter(fn ($html) => \App\Support\Html::text($html) !== '');
    $n = 0;
@endphp
<x-layouts.site :seo="$seo">
    <article>
        <header class="relative overflow-hidden border-b border-border">
            <div class="bg-blueprint pointer-events-none absolute inset-0" aria-hidden="true"></div>
            <div class="container-site relative pt-16 pb-14 sm:pt-24">
                <x-site.breadcrumbs :items="[[__('pages.breadcrumb_home'), route('home')], [__('projects.breadcrumb'), route('projects.index')], [$project->title(), null]]" />
                <div class="mt-10 flex flex-wrap items-center gap-2">
                    @foreach ($project->categories() as $category)
                        <a href="{{ route('projects.index', ['type' => $category->slug]) }}" class="chip hover:text-ink">{{ $category->name }}</a>
                    @endforeach
                </div>
                <h1 class="mt-5 max-w-4xl text-h1 font-semibold tracking-display text-balance">{{ $project->title() }}</h1>
                <p class="mt-6 max-w-3xl text-lede text-muted text-pretty">{{ $project->summary() }}</p>

                {{-- Wrapping flex: items grow to fill each row, so 4–7 facts never leave empty cells. --}}
                <dl class="mt-12 flex flex-wrap gap-px overflow-hidden rounded-lg border border-border bg-border text-sm">
                    @if ($client = $project->clientName())
                        <div class="flex-[1_1_11rem] bg-surface p-5"><dt class="slug">{{ __('projects.meta.client') }}</dt><dd class="mt-2 font-medium">{{ $client }}</dd></div>
                    @endif
                    @if ($project->industry())
                        <div class="flex-[1_1_11rem] bg-surface p-5"><dt class="slug">{{ __('projects.meta.industry') }}</dt><dd class="mt-2 font-medium">{{ $project->industry() }}</dd></div>
                    @endif
                    @if ($project->role())
                        <div class="flex-[2_1_18rem] bg-surface p-5"><dt class="slug">{{ __('projects.meta.role') }}</dt><dd class="mt-2 font-medium">{{ $project->role() }}</dd></div>
                    @endif
                    <div class="flex-[1_1_11rem] bg-surface p-5"><dt class="slug">{{ __('projects.meta.engagement') }}</dt><dd class="mt-2 font-medium">{{ $project->engagementLabel() }}</dd></div>
                    @if ($project->year())
                        <div class="flex-[1_1_11rem] bg-surface p-5"><dt class="slug">{{ __('projects.meta.year') }}</dt><dd class="mt-2 font-mono font-medium">{{ $project->year() }}</dd></div>
                    @endif
                    @if ($live = $project->liveUrl())
                        <div class="flex-[1_1_11rem] bg-surface p-5"><dt class="slug">{{ __('projects.meta.live') }}</dt><dd class="mt-2 font-medium"><a href="{{ $live }}" class="link-arrow" rel="noopener" target="_blank">{{ $project->liveHost() }} <x-lucide-arrow-up-right class="icon-dir size-4" aria-hidden="true" /></a></dd></div>
                    @endif
                    @if ($repo = $project->repoUrl())
                        <div class="flex-[1_1_11rem] bg-surface p-5"><dt class="slug">{{ __('projects.meta.repo') }}</dt><dd class="mt-2 font-medium"><a href="{{ $repo }}" class="link-arrow" rel="noopener" target="_blank">GitHub <x-lucide-arrow-up-right class="icon-dir size-4" aria-hidden="true" /></a></dd></div>
                    @endif
                </dl>
                @unless ($project->isRevealed())
                    <p class="mt-4 flex items-center gap-2 text-sm text-muted"><x-lucide-lock class="size-4" aria-hidden="true" /> {{ __('projects.anonymized_note') }}</p>
                @endunless
            </div>
        </header>

        <div class="container-site mt-12">
            <figure class="overflow-hidden rounded-lg border border-border">
                @if ($cover = $project->cover())
                    <x-picture :media="$cover" :alt="$project->imageAlt(1)" :priority="true" sizes="(min-width: 1200px) 1136px, 100vw" class="h-auto w-full" />
                @else
                    <div class="aspect-[3/2] sm:aspect-[2/1]"><x-plate :project="$project" :index="$index" /></div>
                @endif
            </figure>
        </div>

        @if ($project->facts()->isNotEmpty())
            <section class="container-site mt-16" aria-labelledby="facts-title">
                <h2 id="facts-title" class="slug">{{ __('projects.facts') }}</h2>
                <dl class="mt-5 grid grid-cols-2 gap-px overflow-hidden rounded-lg border border-border bg-border {{ [1 => 'md:grid-cols-1', 2 => 'md:grid-cols-2', 3 => 'md:grid-cols-3', 4 => 'md:grid-cols-4'][$project->facts()->count()] ?? 'md:grid-cols-5' }}">
                    @foreach ($project->facts() as $fact)
                        <div class="bg-surface p-5 sm:p-6">
                            <dd class="font-mono text-3xl font-semibold tracking-tight sm:text-4xl">{{ $fact->value }}</dd>
                            <dt class="mt-2 text-sm text-muted">{{ $fact->label }}</dt>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endif

        <div class="container-site mt-20 grid gap-16 lg:grid-cols-12">
            <div class="space-y-16 lg:col-span-8">
                @foreach ($sections as $name => $html)
                    <section aria-labelledby="{{ $name }}-title">
                        <p class="slug"><span class="text-accent-text">{{ str_pad((string) ++$n, 2, '0', STR_PAD_LEFT) }}</span> — {{ __("projects.{$name}") }}</p>
                        <h2 id="{{ $name }}-title" class="mt-3 text-h2 font-semibold tracking-display">{{ __("projects.{$name}") }}</h2>
                        <div class="prose-site mt-6">{!! $html !!}</div>
                        @if ($name === 'architecture' && ($diagram = $project->architectureImage()))
                            <figure class="mt-8 overflow-hidden rounded-lg border border-border">
                                <x-picture :media="$diagram" :alt="__('projects.architecture_alt', ['title' => $project->title()])" sizes="(min-width: 1024px) 760px, 100vw" class="h-auto w-full" />
                            </figure>
                        @endif
                    </section>
                @endforeach

                @if ($project->features()->isNotEmpty())
                    <section aria-labelledby="features-title">
                        <p class="slug"><span class="text-accent-text">{{ str_pad((string) ++$n, 2, '0', STR_PAD_LEFT) }}</span> — {{ __('projects.features') }}</p>
                        <h2 id="features-title" class="mt-3 text-h2 font-semibold tracking-display">{{ __('projects.features') }}</h2>
                        <ul class="mt-8 grid gap-5 sm:grid-cols-2">
                            @foreach ($project->features() as $feature)
                                <li class="card p-6">
                                    <h3 class="font-semibold">{{ $feature->title }}</h3>
                                    @if ($feature->body)<p class="mt-2 text-[0.95rem] text-muted">{{ $feature->body }}</p>@endif
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if ($project->hasResults())
                    <section aria-labelledby="results-title">
                        <p class="slug"><span class="text-accent-text">{{ str_pad((string) ++$n, 2, '0', STR_PAD_LEFT) }}</span> — {{ __('projects.results') }}</p>
                        <h2 id="results-title" class="mt-3 text-h2 font-semibold tracking-display">{{ __('projects.results') }}</h2>
                        <div class="prose-site mt-6">{!! $project->section('results') !!}</div>
                    </section>
                @endif
            </div>

            <aside class="lg:col-span-4">
                <div class="space-y-10 lg:sticky lg:top-24">
                    @if ($project->technologies()->isNotEmpty())
                        <section aria-labelledby="stack-title">
                            <h2 id="stack-title" class="slug">{{ __('projects.stack') }}</h2>
                            <ul class="mt-4 flex flex-wrap gap-2">
                                @foreach ($project->technologies() as $tech)
                                    <li><a href="{{ route('projects.index', ['tech' => $tech->slug]) }}" class="chip hover:text-ink">{{ $tech->name }}</a></li>
                                @endforeach
                            </ul>
                        </section>
                    @endif
                    @if ($project->services()->isNotEmpty())
                        <section aria-labelledby="services-title">
                            <h2 id="services-title" class="slug">{{ __('projects.services') }}</h2>
                            <ul class="mt-4 space-y-1">
                                @foreach ($project->services() as $service)
                                    <li><a href="{{ route('services.show', $service->slug) }}" class="inline-flex min-h-9 items-center gap-2 font-medium hover:text-accent-text"><x-lucide-arrow-right class="icon-dir size-4 text-muted" aria-hidden="true" />{{ $service->title }}</a></li>
                                @endforeach
                            </ul>
                        </section>
                    @endif
                </div>
            </aside>
        </div>

        @if ($project->screenshots()->count() > 1)
            <section class="container-site mt-24" aria-labelledby="gallery-title">
                <h2 id="gallery-title" class="text-h2 font-semibold tracking-display">{{ __('projects.gallery') }}</h2>
                <div class="mt-10 grid gap-5 sm:grid-cols-2">
                    @foreach ($project->screenshots()->skip(1) as $shot)
                        <figure class="overflow-hidden rounded-lg border border-border">
                            <x-picture :media="$shot" :alt="$project->imageAlt($loop->iteration + 1)" sizes="(min-width: 640px) 50vw, 100vw" class="h-auto w-full" />
                        </figure>
                    @endforeach
                </div>
            </section>
        @endif
    </article>

    @if ($next)
        <nav class="container-site mt-24" aria-labelledby="next-title">
            <a href="{{ $next->url() }}" class="crop-marks group flex flex-col gap-4 rounded-lg border border-border bg-surface p-7 transition-colors hover:border-border-strong sm:flex-row sm:items-center sm:justify-between sm:p-10">
                <span>
                    <span id="next-title" class="slug">{{ __('projects.next') }}</span>
                    <span class="mt-2 block text-h3 font-semibold tracking-tight">{{ $next->title() }}</span>
                </span>
                <x-lucide-arrow-right class="icon-dir size-6 shrink-0 text-accent-text transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1" aria-hidden="true" />
            </a>
        </nav>
    @endif

    <x-site.cta-band />
</x-layouts.site>
