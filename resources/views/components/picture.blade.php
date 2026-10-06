@props(['media', 'alt', 'sizes' => '100vw', 'priority' => false, 'class' => ''])
@php
    /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media $media */
    $width = (int) $media->getCustomProperty('width', 1600);
    $height = (int) $media->getCustomProperty('height', 1000);
    $hasAvif = $media->hasGeneratedConversion('avif');
    $hasWebp = $media->hasGeneratedConversion('webp');
    $fallback = $hasWebp ? $media->getUrl('webp') : $media->getUrl();
@endphp
<picture>
    @if ($hasAvif)
        <source type="image/avif" srcset="{{ $media->getSrcset('avif') }}" sizes="{{ $sizes }}">
    @endif
    @if ($hasWebp)
        <source type="image/webp" srcset="{{ $media->getSrcset('webp') }}" sizes="{{ $sizes }}">
    @endif
    <img src="{{ $fallback }}" alt="{{ $alt }}" width="{{ $width }}" height="{{ $height }}"
        @if ($priority) fetchpriority="high" decoding="async" @else loading="lazy" decoding="async" @endif
        {{ $attributes->merge(['class' => $class]) }}>
</picture>
