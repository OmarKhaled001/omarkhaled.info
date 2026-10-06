<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Support\Locales;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RootRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $locale = Locales::fromRequest($request);

        return redirect()
            ->route('home', ['locale' => $locale], 302)
            ->setVary('Accept-Language');
    }
}
