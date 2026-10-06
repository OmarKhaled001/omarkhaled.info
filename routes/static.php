<?php

use App\Http\Controllers\Site\RootRedirectController;
use Illuminate\Support\Facades\Route;

/*
| Locale-less public endpoints (middleware group "public.static").
*/

Route::get('/', RootRedirectController::class)->name('root');
