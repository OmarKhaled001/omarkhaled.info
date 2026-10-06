<?php

namespace App\View\Composers;

use App\Models\Service;
use App\Support\Profile;
use Illuminate\View\View;

class FooterComposer
{
    public function __construct(private readonly Profile $profile) {}

    public function compose(View $view): void
    {
        $view->with([
            'footerServices' => Service::query()->published()->ordered()->get(['id', 'slug', 'title'])
                ->map(fn (Service $s) => ['title' => $s->title, 'url' => route('services.show', $s->slug)])
                ->all(),
            'socialLinks' => $this->profile->socialLinks(),
        ]);
    }
}
