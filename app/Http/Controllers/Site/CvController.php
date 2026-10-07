<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Support\Profile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** /{locale}/cv: the CV uploaded in Admin -> Settings -> Hiring & CV, under a readable file name. */
class CvController extends Controller
{
    public function __invoke(Profile $profile): StreamedResponse
    {
        $locale = app()->getLocale();
        $path = $profile->cvPath($locale);
        abort_if($path === null, 404);

        $name = Str::slug($profile->name('en')).'-CV-'.Str::upper($locale).'.'.pathinfo($path, PATHINFO_EXTENSION);

        return Storage::disk('public')->download($path, $name, [
            'Cache-Control' => 'public, max-age=3600',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
