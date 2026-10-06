<button type="button" data-theme-toggle aria-pressed="false"
    {{ $attributes->merge(['class' => 'inline-flex size-11 items-center justify-center rounded-md text-muted transition-colors hover:bg-surface-2 hover:text-ink']) }}>
    <span class="sr-only">{{ __('site.theme.toggle') }}</span>
    <x-lucide-moon class="size-[18px] dark:hidden" aria-hidden="true" />
    <x-lucide-sun class="hidden size-[18px] dark:block" aria-hidden="true" />
</button>
