<x-layouts.site :seo="$seo">
    <section class="relative overflow-hidden border-b border-border">
        <div class="bg-blueprint pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="container-site relative py-16 sm:py-20">
            <x-site.breadcrumbs :items="[[__('pages.breadcrumb_home'), route('home')], [__('contact.slug'), null]]" />
            <p class="slug mt-10">{{ __('contact.slug') }}</p>
            <h1 class="mt-3 text-h1 font-semibold tracking-display text-balance">{{ __('contact.title') }}</h1>
            <p class="mt-6 max-w-2xl text-lede text-muted">{{ __('contact.lede') }}</p>
        </div>
    </section>

    <div class="container-site mt-12 grid gap-12 lg:grid-cols-12">
        <div class="lg:col-span-8">
            <livewire:contact-form />
        </div>

        <aside class="lg:col-span-4">
            <dl class="card divide-y divide-border">
                @if ($email = $profile->email())
                    <div class="p-6">
                        <dt class="slug">{{ __('contact.aside.email') }}</dt>
                        <dd class="mt-2"><a href="mailto:{{ $email }}" class="font-medium underline decoration-border-strong underline-offset-4 hover:decoration-accent" dir="ltr">{{ $email }}</a></dd>
                    </div>
                @endif
                <div class="p-6">
                    <dt class="slug">{{ __('contact.aside.response') }}</dt>
                    <dd class="mt-2 font-medium">{{ __('contact.aside.response_value', ['hours' => $profile->responseTimeHours()]) }}</dd>
                </div>
                <div class="p-6">
                    <dt class="slug">{{ __('contact.aside.timezone') }}</dt>
                    <dd class="mt-2 font-medium">{{ __('contact.aside.timezone_value', ['location' => $profile->location(), 'offset' => $profile->utcOffsetLabel()]) }}</dd>
                </div>
                @if ($whatsapp = $profile->whatsappUrl())
                    <div class="p-6">
                        <dt class="slug">{{ __('contact.aside.whatsapp') }}</dt>
                        <dd class="mt-2"><a href="{{ $whatsapp }}" rel="noopener" class="link-arrow">{{ __('contact.aside.whatsapp') }} <x-lucide-arrow-up-right class="icon-dir size-4" aria-hidden="true" /></a></dd>
                    </div>
                @endif
                @if ($booking = $profile->calendlyUrl())
                    <div class="p-6">
                        <dt class="slug">{{ __('contact.aside.booking') }}</dt>
                        <dd class="mt-2"><a href="{{ $booking }}" rel="noopener" class="link-arrow">{{ __('contact.aside.booking') }} <x-lucide-arrow-up-right class="icon-dir size-4" aria-hidden="true" /></a></dd>
                    </div>
                @endif
                @if ($socials = $profile->socialLinks())
                    <div class="p-6">
                        <dt class="slug">{{ __('contact.aside.elsewhere') }}</dt>
                        <dd class="mt-3 flex flex-wrap gap-2">
                            @foreach ($socials as $link)
                                <a href="{{ $link['url'] }}" rel="me noopener" class="chip hover:text-ink">{{ $link['label'] }}</a>
                            @endforeach
                        </dd>
                    </div>
                @endif
            </dl>
        </aside>
    </div>

    @if (app(\App\Support\Spam\Turnstile::class)->enabled())
        <x-slot:scripts>
            @vite('resources/js/turnstile.js')
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        </x-slot:scripts>
    @endif
</x-layouts.site>
