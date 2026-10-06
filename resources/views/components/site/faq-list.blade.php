@props(['faqs'])
<div {{ $attributes->merge(['class' => 'divide-y divide-border border-y border-border']) }}>
    @foreach ($faqs as $faq)
        <details class="group py-1" @if ($loop->first) open @endif>
            <summary class="flex min-h-14 cursor-pointer list-none items-center justify-between gap-6 py-3 text-[1.05rem] font-medium marker:hidden [&::-webkit-details-marker]:hidden">
                <h3>{{ $faq->question }}</h3>
                <x-lucide-plus class="size-5 shrink-0 text-muted transition-transform duration-200 group-open:rotate-45" aria-hidden="true" />
            </summary>
            <div class="pb-6 pe-10 text-muted">
                <p class="max-w-[68ch]">{{ $faq->answer }}</p>
            </div>
        </details>
    @endforeach
</div>
