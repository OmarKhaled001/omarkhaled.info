<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\Seo\Seo;
use Illuminate\Contracts\View\View;

class PrivacyController extends Controller
{
    public function __invoke(): View
    {
        $page = Page::byKey('privacy');
        abort_if($page === null, 404);

        return view('pages.privacy', [
            'seo' => Seo::forRoute($page->meta_title ?: $page->title, (string) $page->meta_description, 'privacy'),
            'page' => $page,
        ]);
    }
}
