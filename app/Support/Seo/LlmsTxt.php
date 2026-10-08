<?php

namespace App\Support\Seo;

use App\Models\Faq;
use App\Models\Page;
use App\Models\Project;
use App\Models\Service;
use App\Presenters\PublicProject;
use App\Support\Html;
use App\Support\Profile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;

/**
 * llms.txt (https://llmstxt.org) and llms-full.txt, generated from the same sources as the pages:
 * identity from Profile (no placeholders), projects through PublicProject (anonymity applies).
 * Written in English, with factual, quotable sentences.
 */
final readonly class LlmsTxt
{
    public function __construct(private Profile $profile) {}

    public function summary(): string
    {
        return $this->withLocale(function (): string {
            $out = [
                '# '.$this->profile->name(),
                '',
                '> '.$this->oneLiner(),
                '',
                $this->about(),
                '',
                '## Services',
                '',
            ];

            foreach ($this->services() as $service) {
                $out[] = "- [{$service->title}](".route('services.show', $service->slug)."): {$service->card_summary}";
            }

            array_push($out, '', '## Selected work', '');
            foreach ($this->projects() as $project) {
                $facts = $project->facts()->map(fn ($f) => "{$f->value} {$f->label}")->implode('; ');
                $out[] = "- [{$project->title()}]({$project->url()}): {$project->summary()}".($facts ? " Verified facts: {$facts}." : '');
            }

            array_push($out, '', '## About', '');
            $out[] = '- [About '.$this->profile->name().']('.route('about').'): background (graphic design → full-stack development), skills by domain and working style with remote teams.';
            $out[] = '- [Contact]('.route('contact').'): project or hiring inquiry form; replies within '.$this->profile->responseTimeHours().' hours.';
            if ($rolesNote = $this->profile->rolesNote('en')) {
                $out[] = "- Hiring: {$rolesNote}.".($this->profile->cvUrl('en') ? ' [CV (PDF)]('.$this->profile->cvUrl('en').')' : '');
            }
            if ($email = $this->profile->email()) {
                $out[] = "- Email: {$email}";
            }
            foreach ($this->profile->socialLinks() as $link) {
                $out[] = "- [{$link['label']}]({$link['url']})";
            }

            array_push($out, '', '## Optional', '');
            $out[] = '- [Full text for language models]('.route('llms.full').'): every service, case study and FAQ in one document.';
            $out[] = '- [Arabic version of the site]('.route('home', ['locale' => 'ar']).'): the same content in Modern Standard Arabic.';
            $out[] = '- [Sitemap]('.route('sitemap').')';

            return implode("\n", $out)."\n";
        });
    }

    public function full(): string
    {
        return $this->withLocale(function (): string {
            $out = ['# '.$this->profile->name().' — full profile', '', '> '.$this->oneLiner(), '', $this->about(), ''];

            if ($page = Page::byKey('about')) {
                array_push($out, '## About', '', Html::text($page->body), '', 'Source: '.route('about'), '');
            }

            array_push($out, '## Services', '');
            foreach ($this->services() as $service) {
                $service->loadMissing(['deliverables', 'processSteps', 'faqs' => fn ($q) => $q->where('is_published', true)]);
                array_push($out, "### {$service->title}", '', 'URL: '.route('services.show', $service->slug), '', $service->intro, '');
                if ($problem = Html::text($service->problem)) {
                    array_push($out, $problem, '');
                }
                $out[] = 'Deliverables:';
                foreach ($service->deliverables as $item) {
                    $out[] = "- {$item->title}: {$item->body}";
                }
                $out[] = '';
                foreach ($service->faqs as $faq) {
                    array_push($out, "Q: {$faq->question}", "A: {$faq->answer}", '');
                }
            }

            array_push($out, '## Case studies', '');
            foreach ($this->projects() as $project) {
                array_push($out, '### '.$project->title(), '', 'URL: '.$project->url(), '', $project->summary(), '');
                $meta = array_filter([
                    'Industry' => $project->industry(),
                    'Role' => $project->role(),
                    'Engagement' => $project->engagementLabel(),
                    'Year' => $project->year(),
                    'Stack' => $project->technologies()->pluck('name')->implode(', '),
                ]);
                foreach ($meta as $label => $value) {
                    $out[] = "- {$label}: {$value}";
                }
                foreach ($project->facts() as $fact) {
                    $out[] = "- {$fact->label}: {$fact->value}";
                }
                $out[] = '';
                foreach (['challenge' => 'Challenge', 'goals' => 'Goals', 'audience' => 'Who it is for', 'solution' => 'Solution', 'journey' => 'How it works', 'architecture' => 'Architecture', 'results' => 'Results'] as $section => $heading) {
                    if ($text = Html::text($project->section($section))) {
                        array_push($out, "#### {$heading}", '', $text, '');
                    }
                }
                if ($project->features()->isNotEmpty()) {
                    array_push($out, '#### Key features', '');
                    foreach ($project->features() as $feature) {
                        $out[] = "- {$feature->title}: {$feature->body}";
                    }
                    $out[] = '';
                }
            }

            array_push($out, '## Frequently asked questions', '');
            foreach (Faq::query()->general()->published()->orderBy('sort_order')->get() as $faq) {
                array_push($out, "Q: {$faq->question}", "A: {$faq->answer}", '');
            }

            array_push($out, '## Contact', '', 'Project inquiries: '.route('contact'));
            if ($email = $this->profile->email()) {
                $out[] = "Email: {$email}";
            }

            return implode("\n", $out)."\n";
        });
    }

    private function oneLiner(): string
    {
        return $this->profile->name().' is a '.mb_lcfirst($this->profile->jobTitle()).' based in '.$this->profile->location()
            .' who builds e-commerce platforms, B2B portals, SaaS products and admin systems for companies worldwide.';
    }

    private function about(): string
    {
        $years = $this->profile->yearsExperience();

        return implode(' ', array_filter([
            $this->profile->name().' works with Laravel, Filament, Livewire, MySQL, REST APIs and Tailwind CSS, and began as a graphic designer.',
            $years ? $this->profile->name()." has {$years}+ years of experience building for the web." : null,
            'Projects run remotely with clients in Europe, the Gulf, North America and elsewhere, in English and Arabic, from '.$this->profile->location().' ('.$this->profile->utcOffsetLabel().').',
            'Client projects are described without client names unless the client approved being named.',
        ]));
    }

    /** @return Collection<int, Service> */
    private function services(): Collection
    {
        return Service::query()->published()->ordered()->get();
    }

    /** @return Collection<int, PublicProject> */
    private function projects(): Collection
    {
        return PublicProject::collection(
            Project::query()->published()->ordered()->with(['features', 'facts', 'technologies', 'categories', 'media'])->get()
        );
    }

    /** llms.txt is English; switch temporarily so translated fields and URLs resolve to /en. */
    private function withLocale(\Closure $build): string
    {
        $previous = app()->getLocale();
        app()->setLocale('en');
        URL::defaults(['locale' => 'en']);

        try {
            return $build();
        } finally {
            app()->setLocale($previous);
        }
    }
}
