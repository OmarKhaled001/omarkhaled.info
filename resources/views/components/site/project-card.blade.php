@props(['project', 'index' => 1, 'feature' => false, 'headingLevel' => 'h3'])
@php
    /** @var \App\Presenters\PublicProject $project */
    $cover = $project->cover();
@endphp
<article {{ $attributes->merge(['class' => 'crop-marks group relative flex flex-col overflow-hidden rounded-lg border border-border bg-surface transition-colors hover:border-border-strong']) }}>
    <div @class(['relative overflow-hidden border-b border-border', 'aspect-[16/10]' => ! $feature, 'aspect-[16/10] md:aspect-auto md:flex-1 md:min-h-72' => $feature])>
        @if ($cover)
            <x-picture :media="$cover" :alt="$project->imageAlt(1)" class="h-full w-full object-cover object-top transition-transform duration-500 group-hover:scale-[1.02]"
                sizes="{{ $feature ? '(min-width: 1024px) 760px, 100vw' : '(min-width: 1024px) 380px, (min-width: 640px) 50vw, 100vw' }}" />
        @else
            <x-plate :project="$project" :index="$index" class="transition-transform duration-500 group-hover:scale-[1.02]" />
        @endif
    </div>
    <div class="flex flex-1 flex-col gap-3 p-5 sm:p-6">
        <div class="flex flex-wrap items-center gap-2 text-xs">
            @foreach ($project->categories() as $category)
                <span class="chip min-h-6 px-2.5 text-xs">{{ $category->name }}</span>
            @endforeach
            @if ($project->year())<span class="font-mono text-muted">{{ $project->year() }}</span>@endif
        </div>
        <{{ $headingLevel }} @class(['font-semibold tracking-tight text-balance', 'text-h3' => $feature, 'text-lg' => ! $feature])>
            <a href="{{ $project->url() }}" class="after:absolute after:inset-0 focus-visible:outline-none">{{ $project->title() }}</a>
        </{{ $headingLevel }}>
        @if ($feature)
            <p class="text-muted">{{ $project->summary() }}</p>
        @endif
        <p class="mt-auto pt-2 font-mono text-xs text-muted">{{ $project->technologies()->take(4)->pluck('name')->implode(' · ') }}</p>
    </div>
</article>
