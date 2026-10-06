<x-layouts.site :seo="$seo">
    <section class="container-site py-16 sm:py-24">
        <x-site.breadcrumbs :items="[[__('pages.breadcrumb_home'), route('home')], [$page->title, null]]" />
        <h1 class="mt-10 text-h1 font-semibold tracking-display">{{ $page->title }}</h1>
        <div class="prose-site mt-10">{!! \App\Support\Html::clean($page->body) !!}</div>
    </section>
</x-layouts.site>
