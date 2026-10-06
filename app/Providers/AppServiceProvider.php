<?php

namespace App\Providers;

use App\Jobs\SyncProjectMediaVisibility;
use App\Models\Category;
use App\Models\Experience;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Project;
use App\Models\ProjectFact;
use App\Models\ProjectFeature;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\Technology;
use App\Models\Testimonial;
use App\Support\Profile;
use App\View\Composers\FooterComposer;
use App\View\Composers\NavigationComposer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelSettings\Events\SettingsSaved;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\ResponseCache\Facades\ResponseCache;

class AppServiceProvider extends ServiceProvider
{
    /** Models whose changes are visible on public (cached) pages. */
    private const array CACHED_MODELS = [
        Project::class, ProjectFeature::class, ProjectFact::class, Service::class, ServiceItem::class,
        Faq::class, Testimonial::class, Experience::class, Page::class, Category::class, Technology::class, Media::class,
    ];

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

        // Any content or settings change clears the full-page cache (small site: clear-all is simplest and correct).
        Event::listen(['eloquent.saved: *', 'eloquent.deleted: *'], function (string $event, array $payload): void {
            if (($payload[0] ?? null) instanceof Model && in_array($payload[0]::class, self::CACHED_MODELS, true)) {
                ResponseCache::clear();
            }
        });
        Event::listen(SettingsSaved::class, fn () => ResponseCache::clear());

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
