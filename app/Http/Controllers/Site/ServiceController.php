<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Presenters\PublicProject;
use App\Support\Seo\Seo;
use Illuminate\Contracts\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('pages.services.index', [
            'seo' => Seo::forRoute(__('services.meta_title'), __('services.meta_description'), 'services.index'),
            'services' => Service::query()->published()->ordered()->with('deliverables')->get(),
        ]);
    }

    public function show(string $slug): View
    {
        $service = Service::query()->published()->where('slug', $slug)
            ->with([
                'deliverables', 'processSteps',
                'faqs' => fn ($q) => $q->where('is_published', true),
                'projects' => fn ($q) => $q->where('is_published', true)->with(['categories', 'technologies', 'media']),
            ])
            ->firstOrFail();

        $seo = Seo::forRoute(
            $service->meta_title ?: $service->title,
            $service->meta_description ?: $service->card_summary,
            'services.show',
            ['slug' => $service->slug],
        );

        return view('pages.services.show', [
            'seo' => $seo,
            'service' => $service,
            'projects' => PublicProject::collection($service->projects),
            'others' => Service::query()->published()->ordered()->whereKeyNot($service->getKey())->get(),
        ]);
    }
}
