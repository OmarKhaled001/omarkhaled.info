<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Support\Seo\Seo;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $seo = Seo::forRoute(__('home.meta_title'), __('home.meta_description'), 'home');

        return view('pages.home', ['seo' => $seo]);
    }
}
