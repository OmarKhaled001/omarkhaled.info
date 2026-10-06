@props(['items'])
{{-- $items: list of [label, url|null]; the last item is the current page. --}}
<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'text-sm']) }}>
    <ol class="flex flex-wrap items-center gap-1.5 text-muted">
        @foreach ($items as $i => [$label, $url])
            <li class="flex items-center gap-1.5">
                @if ($i > 0)<x-lucide-chevron-right class="icon-dir size-3.5 opacity-60" aria-hidden="true" />@endif
                @if ($url && ! $loop->last)
                    <a href="{{ $url }}" class="hover:text-ink">{{ $label }}</a>
                @else
                    <span aria-current="page" class="text-ink">{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
