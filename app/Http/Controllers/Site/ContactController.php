<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Support\Profile;
use App\Support\Seo\Seo;
use Illuminate\Contracts\View\View;

class ContactController extends Controller
{
    public function __invoke(Profile $profile): View
    {
        return view('pages.contact', [
            'seo' => Seo::forRoute(__('contact.meta_title'), __('contact.meta_description'), 'contact'),
            'profile' => $profile,
        ]);
    }
}
