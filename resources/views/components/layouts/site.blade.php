@props(['seo'])
@php
    $locale = app()->getLocale();
    $fontPreload = $locale === 'ar' ? 'resources/fonts/plex-arabic-400.woff2' : 'resources/fonts/geist-latin.woff2';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ \App\Support\Locales::dir() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>{!! \App\Support\Design\Theme::script() !!}</script>
    <x-seo :seo="$seo" />
    <link rel="preload" href="{{ Vite::asset($fontPreload) }}" as="font" type="font/woff2" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>{!! \App\Support\Design\Theme::accentCss() !!}</style>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="theme-color" content="#F7F6F2" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0A0A0B" media="(prefers-color-scheme: dark)">
    {{ $head ?? '' }}
</head>
<body class="noise flex min-h-dvh flex-col">
    <a href="#main" class="sr-only z-50 rounded-md bg-accent px-4 py-2 font-medium text-on-accent focus:not-sr-only focus:fixed focus:start-4 focus:top-4">
        {{ __('site.skip_to_content') }}
    </a>

    @include('partials.header')

    <main id="main" tabindex="-1" class="flex-1 focus:outline-none">
        {{ $slot }}
    </main>

    @include('partials.footer')

    {{ $scripts ?? '' }}
</body>
</html>
