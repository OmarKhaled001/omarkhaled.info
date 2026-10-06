<?php

use App\Http\Controllers\Site\HomeController;
use App\Support\Locales;
use Illuminate\Support\Facades\Route;

/*
| Localized public pages (middleware group "public": no session/cookies, cacheable).
*/

Route::prefix('{locale}')
    ->where(['locale' => Locales::PATTERN])
    ->group(function (): void {
        Route::get('/', HomeController::class)->name('home');
    });
