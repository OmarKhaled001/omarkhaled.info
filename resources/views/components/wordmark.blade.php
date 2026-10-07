@php
    $logo = \App\Support\Design\Brand::logo();
    $darkLogo = $logo ? \App\Support\Design\Brand::logo('dark') : null;
@endphp
{{-- Uploaded logo (Admin → Settings → Design) + name; otherwise the registration mark of the print-shop origin. --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    @if ($logo)
        {{-- Without a dark variant, white backgrounds blend away (multiply) and dark mode inverts luminance but keeps the hues. --}}
        <img src="{{ $logo['url'] }}" width="{{ $logo['width'] }}" height="{{ $logo['height'] }}" alt="" decoding="async"
            @class(['h-8 w-auto shrink-0', 'dark:hidden' => $darkLogo, 'mix-blend-multiply dark:mix-blend-screen dark:invert dark:hue-rotate-180' => ! $darkLogo])>
        @if ($darkLogo)
            <img src="{{ $darkLogo['url'] }}" width="{{ $darkLogo['width'] }}" height="{{ $darkLogo['height'] }}" alt="" decoding="async" class="hidden h-8 w-auto shrink-0 dark:block">
        @endif
    @else
        <svg class="size-[22px] shrink-0 text-accent" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="6.5" stroke="currentColor" stroke-width="1.6"/>
            <path d="M12 1.5v21M1.5 12h21" stroke="currentColor" stroke-width="1.6"/>
            <circle cx="12" cy="12" r="2" fill="currentColor"/>
        </svg>
    @endif
    <span class="text-[0.98rem] font-semibold tracking-tight text-ink">{{ __('site.name') }}</span>
</span>
