@php
    $seo = \App\Support\Seo\Seo::forRoute(__('site.not_found.meta_title'), __('site.not_found.meta_description'), 'home')->noindex();
    $seo->canonical = url()->current();
    $seo->alternates = [];
@endphp
<x-layouts.site :seo="$seo">
    <section class="relative overflow-hidden">
        <div class="bg-blueprint pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="container-site relative grid min-h-[70dvh] items-center gap-12 py-20 md:grid-cols-12">
            <div class="md:col-span-7">
                <p class="slug">{{ __('site.not_found.slug') }}</p>
                <h1 class="mt-5 text-h1 font-semibold tracking-display text-balance">{{ __('site.not_found.title') }}</h1>
                <p class="mt-6 max-w-xl text-lede text-muted">{{ __('site.not_found.body') }}</p>
                <div class="mt-10 flex flex-wrap gap-3">
                    <a href="{{ route('home') }}" class="btn-primary">{{ __('site.not_found.home') }}</a>
                    @if (Route::has('projects.index'))
                        <a href="{{ route('projects.index') }}" class="btn-secondary">{{ __('site.not_found.work') }}</a>
                    @endif
                </div>
                @if (Route::has('contact'))
                    <p class="mt-8"><a href="{{ route('contact') }}" class="link-arrow">{{ __('site.not_found.contact') }}
                        <x-lucide-arrow-right class="icon-dir size-4" aria-hidden="true" /></a></p>
                @endif
            </div>
            {{-- Misregistered plate: the colour layer slipped, as on a rejected print proof. --}}
            <div class="hidden md:col-span-5 md:block" aria-hidden="true">
                <svg viewBox="0 0 320 320" class="w-full max-w-sm">
                    <g fill="none" stroke-width="1.5">
                        <g stroke="var(--border-strong)">
                            <path d="M40 20v30M20 40h30M280 20v30M300 40h-30M40 300v-30M20 280h30M280 300v-30M300 280h-30"/>
                        </g>
                        <text x="160" y="190" text-anchor="middle" font-family="var(--font-sans)" font-size="120" font-weight="700" fill="var(--text)" stroke="none">404</text>
                        <text x="171" y="181" text-anchor="middle" font-family="var(--font-sans)" font-size="120" font-weight="700" fill="none" stroke="var(--accent)">404</text>
                        <circle cx="160" cy="250" r="14" stroke="var(--accent)"/>
                        <path d="M160 228v44M138 250h44" stroke="var(--accent)"/>
                    </g>
                </svg>
            </div>
        </div>
    </section>
</x-layouts.site>
