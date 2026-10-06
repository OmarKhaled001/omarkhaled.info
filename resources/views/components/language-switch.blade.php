@php
    $target = \App\Support\Locales::other();
    $name = \App\Support\Locales::nativeName($target);
@endphp
<a href="{{ \App\Support\Locales::switchUrl($target) }}" hreflang="{{ $target }}" lang="{{ $target }}" rel="alternate"
    {{ $attributes->merge(['class' => 'inline-flex min-h-11 items-center rounded-md px-3 text-sm font-medium text-muted transition-colors hover:bg-surface-2 hover:text-ink']) }}>
    <span class="sr-only" lang="{{ app()->getLocale() }}">{{ __('site.language.switch_to', ['language' => $name]) }}</span>
    <span aria-hidden="true">{{ $name }}</span>
</a>
