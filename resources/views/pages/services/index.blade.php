<x-layouts.site :seo="$seo">
    <section class="relative overflow-hidden border-b border-border">
        <div class="bg-blueprint pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="container-site relative py-16 sm:py-24">
            <x-site.breadcrumbs :items="[[__('pages.breadcrumb_home'), route('home')], [__('services.breadcrumb'), null]]" />
            <p class="slug mt-10">{{ __('services.slug') }}</p>
            <h1 class="mt-3 text-h1 font-semibold tracking-display text-balance">{{ __('services.title') }}</h1>
            <p class="mt-6 max-w-2xl text-lede text-muted">{{ __('services.lede') }}</p>
        </div>
    </section>

    <section class="container-site mt-16">
        <ul class="grid gap-5 md:grid-cols-2">
            @foreach ($services as $service)
                <li class="crop-marks card group relative flex flex-col p-7 sm:p-8" data-reveal style="--reveal-delay: {{ $loop->index }}">
                    <div class="flex items-start justify-between gap-6">
                        <span class="inline-flex size-11 items-center justify-center rounded-md border border-border bg-bg text-accent-text" aria-hidden="true">
                            <x-dynamic-component :component="'lucide-'.($service->icon ?: 'code')" class="size-5" />
                        </span>
                        <span class="font-mono text-sm text-muted">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <h2 class="mt-6 text-h3 font-semibold tracking-tight">
                        <a href="{{ route('services.show', $service->slug) }}" class="after:absolute after:inset-0">{{ $service->title }}</a>
                    </h2>
                    <p class="mt-3 text-muted">{{ $service->card_summary }}</p>
                    <ul class="mt-6 space-y-2 border-t border-border pt-5 text-[0.95rem]">
                        @foreach ($service->deliverables->take(3) as $item)
                            <li class="flex gap-2.5"><x-lucide-check class="mt-1 size-4 shrink-0 text-accent-text" aria-hidden="true" />{{ $item->title }}</li>
                        @endforeach
                    </ul>
                    <span class="link-arrow mt-6">{{ __('site.cta.learn_more') }} <x-lucide-arrow-right class="icon-dir size-4" aria-hidden="true" /></span>
                </li>
            @endforeach
        </ul>
    </section>

    <x-site.cta-band />
</x-layouts.site>
