<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Project;
use App\Models\Service;
use App\Models\Technology;
use App\Models\Testimonial;
use App\Presenters\PublicProject;
use App\Support\Profile;
use App\Support\Seo\SchemaGraph;
use App\Support\Seo\Seo;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(Profile $profile): View
    {
        $seo = Seo::forRoute(__('home.meta_title'), __('home.meta_description'), 'home');
        $seo->isHome = true;
        $faqs = Faq::query()->general()->published()->orderBy('sort_order')->get();
        $seo->addSchema(app(SchemaGraph::class)->faqPage($faqs, $seo->canonical));

        return view('pages.home', [
            'seo' => $seo,
            'profile' => $profile,
            'services' => Service::query()->published()->ordered()->get(),
            'projects' => PublicProject::collection(
                Project::query()->published()->featured()->ordered()
                    ->with(['categories', 'technologies', 'media'])
                    ->limit(5)->get()
            ),
            'stack' => Technology::query()->where('show_in_stack', true)->orderBy('sort_order')->get(),
            'faqs' => $faqs,
            'testimonials' => Testimonial::query()->visible()->with('media')->get(),
        ]);
    }
}
