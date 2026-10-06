<?php

namespace App\View\Composers;

use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class NavigationComposer
{
    public function compose(View $view): void
    {
        $items = [
            ['route' => 'projects.index', 'label' => __('site.nav.work'), 'match' => 'projects.*'],
            ['route' => 'services.index', 'label' => __('site.nav.services'), 'match' => 'services.*'],
            ['route' => 'about', 'label' => __('site.nav.about'), 'match' => 'about'],
            ['route' => 'contact', 'label' => __('site.nav.contact'), 'match' => 'contact'],
        ];

        $current = Route::currentRouteName();

        $view->with('navigation', collect($items)
            ->filter(fn (array $item): bool => Route::has($item['route']))
            ->map(fn (array $item): array => $item + [
                'url' => route($item['route']),
                'active' => $current !== null && Route::is($item['match']),
            ])
            ->values()
            ->all());
    }
}
