<?php

namespace App\Providers;

use App\Support\Profile;
use App\View\Composers\FooterComposer;
use App\View\Composers\NavigationComposer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(Profile::class);
    }

    public function boot(): void
    {
        // Surface N+1 queries and silent attribute bugs everywhere except production.
        Model::shouldBeStrict(! $this->app->isProduction());

        View::composer(['partials.header', 'partials.footer'], NavigationComposer::class);
        View::composer('partials.footer', FooterComposer::class);
    }
}
