<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use App\Models\Page;
use App\Models\Technology;
use App\Support\Profile;
use App\Support\Seo\Seo;
use Illuminate\Contracts\View\View;

class AboutController extends Controller
{
    public function __invoke(Profile $profile): View
    {
        $page = Page::byKey('about');
        abort_if($page === null, 404);

        return view('pages.about', [
            'seo' => Seo::forRoute($page->meta_title ?: $page->title, (string) $page->meta_description, 'about'),
            'page' => $page,
            'profile' => $profile,
            'skills' => Technology::query()->where('show_on_about', true)->orderBy('sort_order')->get()->groupBy(fn (Technology $t): string => $t->domain->value),
            'experience' => Experience::query()->published()->get(),
        ]);
    }
}
