{{-- Registration mark + name: the print-shop origin of the brand. --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <svg class="size-[22px] shrink-0 text-accent" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <circle cx="12" cy="12" r="6.5" stroke="currentColor" stroke-width="1.6"/>
        <path d="M12 1.5v21M1.5 12h21" stroke="currentColor" stroke-width="1.6"/>
        <circle cx="12" cy="12" r="2" fill="currentColor"/>
    </svg>
    <span class="text-[0.98rem] font-semibold tracking-tight text-ink">{{ __('site.name') }}</span>
</span>
