<?php

namespace App\Providers;

use App\Jobs\SyncProjectMediaVisibility;
use App\Models\Project;
use App\Support\Profile;
use App\View\Composers\FooterComposer;
use App\View\Composers\NavigationComposer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

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

        // Files uploaded after a project was revealed must land on the right disk too.
        Event::listen(function (MediaHasBeenAddedEvent $event): void {
            // Record intrinsic dimensions so <img> always has width/height (no layout shift).
            if (str_starts_with((string) $event->media->mime_type, 'image/') && ($size = @getimagesize($event->media->getPath()))) {
                $event->media->setCustomProperty('width', $size[0])->setCustomProperty('height', $size[1])->saveQuietly();
            }

            if ($event->media->model_type === (new Project)->getMorphClass()
                && array_key_exists($event->media->collection_name, Project::IDENTIFIABLE_COLLECTIONS)) {
                SyncProjectMediaVisibility::dispatch((int) $event->media->model_id);
            }
        });
    }
}
