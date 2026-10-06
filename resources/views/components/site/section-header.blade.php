@props(['slug', 'title', 'lede' => null, 'index' => null, 'id' => null, 'level' => 'h2'])
<div {{ $attributes->merge(['class' => 'max-w-3xl']) }}>
    <p class="slug">@if ($index)<span class="text-accent-text">{{ str_pad((string) $index, 2, '0', STR_PAD_LEFT) }}</span> — @endif{{ $slug }}</p>
    <{{ $level }} @if ($id) id="{{ $id }}" @endif class="mt-3 text-h2 font-semibold tracking-display text-balance">{{ $title }}</{{ $level }}>
    @if ($lede)
        <p class="mt-4 text-lede text-muted text-pretty">{{ $lede }}</p>
    @endif
</div>
