<?php

use App\Http\Controllers\Site\LlmsController;
use App\Http\Controllers\Site\RobotsController;
use App\Http\Controllers\Site\RootRedirectController;
use App\Http\Controllers\Site\SitemapController;
use Illuminate\Support\Facades\Route;

/*
| Locale-less public endpoints (middleware group "public.static").
*/

Route::get('/', RootRedirectController::class)->name('root');
Route::get('sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('robots.txt', RobotsController::class)->name('robots');
Route::get('llms.txt', [LlmsController::class, 'summary'])->name('llms');
Route::get('llms-full.txt', [LlmsController::class, 'full'])->name('llms.full');
