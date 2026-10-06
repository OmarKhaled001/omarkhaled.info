<header class="sticky top-0 z-40 border-b border-border/70 bg-bg/80 backdrop-blur-md supports-[backdrop-filter]:bg-bg/70">
    <div class="container-site flex h-16 items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="-ms-1 rounded-md p-1" @if (Route::is('home')) aria-current="page" @endif>
            <x-wordmark />
        </a>

        <nav aria-label="{{ __('site.nav.label') }}" class="hidden md:block">
            <ul class="flex items-center gap-1">
                @foreach ($navigation as $item)
                    <li>
                        <a href="{{ $item['url'] }}"
                            @class([
                                'inline-flex min-h-11 items-center rounded-md px-3 text-[0.94rem] transition-colors hover:bg-surface-2 hover:text-ink',
                                'text-ink font-medium' => $item['active'],
                                'text-muted' => ! $item['active'],
                            ])
                            @if ($item['active']) aria-current="page" @endif>
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="flex items-center gap-1">
            <x-language-switch />
            <x-theme-toggle />
            @if (Route::has('contact'))
                <a href="{{ route('contact') }}" class="btn-primary ms-2 hidden min-h-10 px-4 text-sm lg:inline-flex">
                    {{ __('site.cta.start_project') }}
                </a>
            @endif
            {{-- Without JS this jumps to the footer navigation; with JS it opens the menu dialog. --}}
            <a href="#footer-nav" data-menu-open aria-haspopup="dialog" aria-expanded="false" aria-controls="site-menu"
                class="inline-flex size-11 items-center justify-center rounded-md text-ink hover:bg-surface-2 md:hidden">
                <span class="sr-only">{{ __('site.nav.open_menu') }}</span>
                <x-lucide-menu class="size-5" aria-hidden="true" />
            </a>
        </div>
    </div>
</header>

<dialog id="site-menu" class="site-menu m-0 ms-auto h-dvh max-h-none w-[min(22rem,88vw)] max-w-none border-s border-border bg-bg p-0 text-ink" aria-label="{{ __('site.nav.menu') }}">
    <div class="flex h-16 items-center justify-between border-b border-border px-4">
        <span class="slug">{{ __('site.nav.menu') }}</span>
        <button type="button" data-menu-close class="inline-flex size-11 items-center justify-center rounded-md hover:bg-surface-2">
            <span class="sr-only">{{ __('site.nav.close_menu') }}</span>
            <x-lucide-x class="size-5" aria-hidden="true" />
        </button>
    </div>
    <nav aria-label="{{ __('site.nav.label') }}" class="px-2 py-4">
        <ul class="space-y-1">
            <li><a href="{{ route('home') }}" class="flex min-h-12 items-center rounded-md px-3 text-lg hover:bg-surface-2">{{ __('site.nav.home') }}</a></li>
            @foreach ($navigation as $item)
                <li>
                    <a href="{{ $item['url'] }}" class="flex min-h-12 items-center rounded-md px-3 text-lg hover:bg-surface-2"
                        @if ($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a>
                </li>
            @endforeach
        </ul>
        @if (Route::has('contact'))
            <a href="{{ route('contact') }}" class="btn-primary mx-3 mt-6 flex">{{ __('site.cta.start_project') }}</a>
        @endif
    </nav>
</dialog>
