<footer class="mt-24 border-t border-border bg-surface/60" id="footer-nav">
    <div class="container-site grid gap-12 py-14 md:grid-cols-12">
        <div class="md:col-span-5">
            <x-wordmark />
            <p class="mt-4 max-w-sm text-[0.95rem] text-muted">{{ __('site.footer.tagline') }}</p>
            @if (Route::has('contact'))
                <a href="{{ route('contact') }}" class="btn-primary mt-6">
                    {{ __('site.cta.start_project') }}
                    <x-lucide-arrow-up-right class="icon-dir size-4" aria-hidden="true" />
                </a>
            @endif
        </div>

        <nav class="md:col-span-2" aria-labelledby="footer-explore">
            <h2 id="footer-explore" class="slug">{{ __('site.footer.explore') }}</h2>
            <ul class="mt-4 space-y-1 text-[0.95rem]">
                <li><a href="{{ route('home') }}" class="inline-flex min-h-9 items-center text-muted hover:text-ink">{{ __('site.nav.home') }}</a></li>
                @foreach ($navigation as $item)
                    <li><a href="{{ $item['url'] }}" class="inline-flex min-h-9 items-center text-muted hover:text-ink">{{ $item['label'] }}</a></li>
                @endforeach
                @if (Route::has('privacy'))
                    <li><a href="{{ route('privacy') }}" class="inline-flex min-h-9 items-center text-muted hover:text-ink">{{ __('site.nav.privacy') }}</a></li>
                @endif
            </ul>
        </nav>

        @if ($footerServices)
            <nav class="md:col-span-3" aria-labelledby="footer-services">
                <h2 id="footer-services" class="slug">{{ __('site.footer.services') }}</h2>
                <ul class="mt-4 space-y-1 text-[0.95rem]">
                    @foreach ($footerServices as $service)
                        <li><a href="{{ $service['url'] }}" class="inline-flex min-h-9 items-center text-muted hover:text-ink">{{ $service['title'] }}</a></li>
                    @endforeach
                </ul>
            </nav>
        @endif

        <div class="md:col-span-2">
            <h2 class="slug">{{ __('site.footer.elsewhere') }}</h2>
            <ul class="mt-4 space-y-1 text-[0.95rem]">
                @foreach ($socialLinks as $link)
                    <li><a href="{{ $link['url'] }}" rel="me noopener" class="inline-flex min-h-9 items-center text-muted hover:text-ink">{{ $link['label'] }}</a></li>
                @endforeach
                @if (Route::has('llms'))
                    <li><a href="{{ route('llms') }}" class="inline-flex min-h-9 items-center text-muted hover:text-ink">{{ __('site.footer.for_ai') }}</a></li>
                @endif
                <li><x-language-switch class="!min-h-9 !px-0 !text-[0.95rem] hover:!bg-transparent" /></li>
            </ul>
        </div>
    </div>
    <div class="border-t border-border">
        <div class="container-site flex flex-col gap-2 py-6 text-sm text-muted sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ now()->year }} {{ __('site.name') }}. {{ __('site.footer.rights') }}</p>
            <p>{{ __('site.footer.built_with') }}</p>
        </div>
    </div>
</footer>
