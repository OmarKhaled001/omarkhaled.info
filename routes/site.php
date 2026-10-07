<?php

use App\Http\Controllers\Site\AboutController;
use App\Http\Controllers\Site\CvController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\PrivacyController;
use App\Http\Controllers\Site\ProjectController;
use App\Http\Controllers\Site\ServiceController;
use App\Support\Locales;
use Illuminate\Support\Facades\Route;

/*
| Localized public pages (middleware group "public": no session/cookies, cacheable).
*/

Route::prefix('{locale}')
    ->where(['locale' => Locales::PATTERN])
    ->group(function (): void {
        Route::get('/', HomeController::class)->name('home');
        Route::get('about', AboutController::class)->name('about');
        Route::get('services', [ServiceController::class, 'index'])->name('services.index');
        Route::get('services/{slug}', [ServiceController::class, 'show'])->name('services.show')->where('slug', '[a-z0-9-]+');
        Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('projects/{slug}', [ProjectController::class, 'show'])->name('projects.show')->where('slug', '[a-z0-9-]+');
        Route::get('privacy', PrivacyController::class)->name('privacy');
        Route::get('cv', CvController::class)->name('cv');
    });
