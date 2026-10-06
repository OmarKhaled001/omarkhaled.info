<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Project;
use App\Models\Technology;
use App\Presenters\PublicProject;
use App\Support\Seo\OgImage;
use App\Support\Seo\SchemaGraph;
use App\Support\Seo\Seo;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->string('type')->toString() ?: null;
        $tech = $request->string('tech')->toString() ?: null;

        $published = fn (Builder $q) => $q->where('is_published', true);

        $categories = Category::query()->whereHas('projects', $published)->orderBy('sort_order')->get();
        $technologies = Technology::query()->whereHas('projects', $published)
            ->withCount(['projects' => $published])->orderByDesc('projects_count')->orderBy('sort_order')->limit(14)->get();

        $projects = Project::query()->published()->ordered()
            ->when($type, fn (Builder $q) => $q->whereHas('categories', fn (Builder $c) => $c->where('slug', $type)))
            ->when($tech, fn (Builder $q) => $q->whereHas('technologies', fn (Builder $t) => $t->where('slug', $tech)))
            ->with(['categories', 'technologies', 'media'])
            ->get();

        $presented = PublicProject::collection($projects);
        $seo = Seo::forRoute(__('projects.meta_title'), __('projects.meta_description'), 'projects.index')
            ->asPage('CollectionPage', __('projects.slug'))
            ->withBreadcrumbs([[__('pages.breadcrumb_home'), route('home')], [__('projects.breadcrumb'), route('projects.index')]]);
        $seo->addSchema(app(SchemaGraph::class)->itemList($presented, $seo->canonical));

        // Filtered views are navigation aids, not landing pages: keep them out of the index.
        if ($type || $tech) {
            $seo->noindex();
        }

        return view('pages.projects.index', [
            'seo' => $seo,
            'projects' => $presented,
            'categories' => $categories,
            'technologies' => $technologies,
            'activeType' => $type,
            'activeTech' => $tech,
        ]);
    }

    public function show(string $slug): View
    {
        $project = Project::query()->published()->where('slug', $slug)
            ->with(['features', 'facts', 'categories', 'technologies', 'services', 'media'])
            ->firstOrFail();

        $presented = new PublicProject($project);

        $next = Project::query()->published()->ordered()
            ->where(fn (Builder $q) => $q->where('sort_order', '>', $project->sort_order)
                ->orWhere(fn (Builder $q) => $q->where('sort_order', $project->sort_order)->where('id', '>', $project->id)))
            ->with(['categories', 'technologies', 'media'])
            ->first()
            ?? Project::query()->published()->ordered()->whereKeyNot($project->getKey())->with(['categories', 'technologies', 'media'])->first();

        $seo = Seo::forRoute($presented->metaTitle(), $presented->metaDescription(), 'projects.show', ['slug' => $project->slug])
            ->asPage('WebPage', __('projects.eyebrow'))
            ->withBreadcrumbs([
                [__('pages.breadcrumb_home'), route('home')],
                [__('projects.breadcrumb'), route('projects.index')],
                [$presented->title(), route('projects.show', ['slug' => $project->slug])],
            ]);
        $seo->type = 'article';
        $seo->withImage(app(OgImage::class)->url($presented->title(), __('projects.eyebrow'), app()->getLocale()), $presented->title());
        $seo->addSchema(app(SchemaGraph::class)->project($presented, $seo->image));

        return view('pages.projects.show', [
            'seo' => $seo,
            'project' => $presented,
            'index' => Project::query()->published()->where('sort_order', '<', $project->sort_order)->count() + 1,
            'next' => $next ? new PublicProject($next) : null,
        ]);
    }
}
