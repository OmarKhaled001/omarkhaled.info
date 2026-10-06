<?php

use App\Http\Controllers\Site\ContactController;
use App\Support\Locales;
use Illuminate\Support\Facades\Route;

/*
| Session-backed routes (middleware group "web"). Public, cacheable pages live in routes/site.php.
| The contact page needs a session and CSRF for its Livewire form, so it is never full-page cached.
*/

Route::prefix('{locale}')
    ->where(['locale' => Locales::PATTERN])
    ->middleware('locale')
    ->group(function (): void {
        Route::get('contact', ContactController::class)->name('contact');
    });
