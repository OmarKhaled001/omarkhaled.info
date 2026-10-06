@php($profile = app(\App\Support\Profile::class))
<section class="container-site mt-28" aria-labelledby="cta-title">
    <div class="relative overflow-hidden rounded-lg border border-border bg-surface px-6 py-14 sm:px-12 sm:py-16">
        <div class="bg-blueprint pointer-events-none absolute inset-0 opacity-70" aria-hidden="true"></div>
        <div class="relative grid gap-10 md:grid-cols-12 md:items-end">
            <div class="md:col-span-8">
                <p class="slug">{{ __('home.cta.slug') }}</p>
                <h2 id="cta-title" class="mt-3 text-h2 font-semibold tracking-display text-balance">{{ __('home.cta.title') }}</h2>
                <p class="mt-4 max-w-xl text-lede text-muted">{{ __('home.cta.body', ['hours' => $profile->responseTimeHours()]) }}</p>
            </div>
            <div class="flex flex-col gap-3 md:col-span-4 md:items-end">
                <a href="{{ route('contact') }}" class="btn-primary">
                    {{ $profile->heroCtaText() }}
                    <x-lucide-arrow-right class="icon-dir size-4" aria-hidden="true" />
                </a>
                @if ($email = $profile->email())
                    <p class="text-sm text-muted">{{ __('home.cta.email') }} <a href="mailto:{{ $email }}" class="font-medium text-ink underline decoration-border-strong underline-offset-4 hover:decoration-accent">{{ $email }}</a></p>
                @endif
            </div>
        </div>
    </div>
</section>
