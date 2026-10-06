@props(['seo'])
@php
    /** @var \App\Support\Seo\Seo $seo */
    $locale = app()->getLocale();
@endphp
<title>{{ $seo->fullTitle() }}</title>
<meta name="description" content="{{ $seo->description }}">
<link rel="canonical" href="{{ $seo->canonical }}">
@foreach ($seo->alternates as $hreflang => $href)
<link rel="alternate" hreflang="{{ $hreflang }}" href="{{ $href }}">
@endforeach
@if ($seo->xDefault())
<link rel="alternate" hreflang="x-default" href="{{ $seo->xDefault() }}">
@endif
<meta name="robots" content="{{ $seo->index ? 'index, follow, max-image-preview:large, max-snippet:-1' : 'noindex, follow' }}">
<meta property="og:type" content="{{ $seo->type }}">
<meta property="og:site_name" content="{{ __('site.name') }}">
<meta property="og:title" content="{{ $seo->fullTitle() }}">
<meta property="og:description" content="{{ $seo->description }}">
<meta property="og:url" content="{{ $seo->canonical }}">
<meta property="og:locale" content="{{ \App\Support\Locales::OG[$locale] }}">
@foreach (\App\Support\Locales::SUPPORTED as $alt)
@if ($alt !== $locale)
<meta property="og:locale:alternate" content="{{ \App\Support\Locales::OG[$alt] }}">
@endif
@endforeach
@if ($seo->image)
<meta property="og:image" content="{{ $seo->image }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ $seo->imageAlt ?? $seo->title }}">
<meta name="twitter:image" content="{{ $seo->image }}">
@endif
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo->fullTitle() }}">
<meta name="twitter:description" content="{{ $seo->description }}">
{{ $slot ?? '' }}
@if ($seo->schema)
<script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@graph' => $seo->schema], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endif
