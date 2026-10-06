<?php

namespace App\Support\Seo;

use App\Enums\SchemaType;
use App\Models\Faq;
use App\Models\Service;
use App\Models\Technology;
use App\Presenters\PublicProject;
use App\Support\Html;
use App\Support\Locales;
use App\Support\Profile;
use Illuminate\Support\Collection;

/**
 * schema.org JSON-LD for one page as a single @graph. Identity comes only from Profile,
 * so placeholder values never reach structured data, and projects only through PublicProject.
 */
final readonly class SchemaGraph
{
    public function __construct(private Profile $profile) {}

    public static function id(string $fragment): string
    {
        return rtrim(url('/'), '/').'/#'.$fragment;
    }

    /** @return list<array<string, mixed>> */
    public function forPage(Seo $seo): array
    {
        $locale = app()->getLocale();

        $page = array_filter([
            '@type' => $seo->pageType,
            '@id' => $seo->canonical.'#webpage',
            'url' => $seo->canonical,
            'name' => $seo->fullTitle(),
            'description' => $seo->description,
            'inLanguage' => $locale,
            'isPartOf' => ['@id' => self::id('website')],
            'about' => $seo->pageType === 'ProfilePage' || $seo->isHome ? ['@id' => self::id('person')] : null,
            'breadcrumb' => $seo->breadcrumbs ? ['@id' => $seo->canonical.'#breadcrumb'] : null,
            'primaryImageOfPage' => $seo->image ? ['@type' => 'ImageObject', 'url' => $seo->image] : null,
        ]);

        $graph = [$this->person(), $this->business(), $this->website(), $page];

        if ($seo->breadcrumbs) {
            $graph[] = [
                '@type' => 'BreadcrumbList',
                '@id' => $seo->canonical.'#breadcrumb',
                'itemListElement' => collect($seo->breadcrumbs)->values()->map(fn (array $crumb, int $i) => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $crumb[0],
                    'item' => $crumb[1],
                ])->all(),
            ];
        }

        return [...$graph, ...$seo->schema];
    }

    /** @return array<string, mixed> */
    public function person(): array
    {
        $knowsAbout = Technology::query()->where('show_in_stack', true)->orderBy('sort_order')->pluck('name')->all();

        return array_filter([
            '@type' => 'Person',
            '@id' => self::id('person'),
            'name' => $this->profile->name('en'),
            'alternateName' => $this->profile->name('ar'),
            'jobTitle' => $this->profile->jobTitle(),
            'url' => route('home', ['locale' => Locales::DEFAULT]),
            'email' => $this->profile->email() ? 'mailto:'.$this->profile->email() : null,
            'address' => ['@type' => 'PostalAddress', 'addressCountry' => $this->profile->countryCode()],
            'knowsAbout' => array_values(array_unique([...$knowsAbout, 'Laravel development', 'Filament admin panels', 'E-commerce', 'SaaS', 'REST APIs'])),
            'knowsLanguage' => ['en', 'ar'],
            'sameAs' => $this->profile->sameAs() ?: null,
        ]);
    }

    /** @return array<string, mixed> */
    public function business(): array
    {
        return array_filter([
            '@type' => 'ProfessionalService',
            '@id' => self::id('business'),
            'name' => $this->profile->name('en').' — '.__('site.role', [], 'en'),
            'description' => __('home.meta_description', [], 'en'),
            'url' => route('home', ['locale' => Locales::DEFAULT]),
            'founder' => ['@id' => self::id('person')],
            'provider' => ['@id' => self::id('person')],
            'areaServed' => ['@type' => 'Place', 'name' => 'Worldwide'],
            'availableLanguage' => ['English', 'Arabic'],
            'email' => $this->profile->email(),
            'sameAs' => $this->profile->sameAs() ?: null,
        ]);
    }

    /** @return array<string, mixed> */
    public function website(): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => self::id('website'),
            'url' => url('/'),
            'name' => $this->profile->name('en'),
            'inLanguage' => Locales::SUPPORTED,
            'publisher' => ['@id' => self::id('person')],
        ];
    }

    /** @return array<string, mixed> */
    public function project(PublicProject $project, ?string $image): array
    {
        $isApp = $project->schemaType() === SchemaType::SoftwareApplication;

        return array_filter([
            '@type' => $project->schemaType()->value,
            '@id' => $project->url().'#work',
            'name' => $project->title(),
            'headline' => $project->title(),
            'description' => $project->summary(),
            'url' => $project->url(),
            'inLanguage' => app()->getLocale(),
            'creator' => ['@id' => self::id('person')],
            'author' => ['@id' => self::id('person')],
            'dateCreated' => $project->year() ? (string) $project->year() : null,
            'keywords' => $project->technologies()->pluck('name')->implode(', ') ?: null,
            'genre' => $project->categories()->pluck('name')->implode(', ') ?: null,
            'image' => $image,
            'sourceOrganization' => ($client = $project->clientName()) ? ['@type' => 'Organization', 'name' => $client] : null,
            'applicationCategory' => $isApp ? 'BusinessApplication' : null,
            'operatingSystem' => $isApp ? 'Web' : null,
            'abstract' => Html::text($project->section('challenge')) ?: null,
        ]);
    }

    /**
     * @param  Collection<int, PublicProject>  $projects
     * @return array<string, mixed>
     */
    public function service(Service $service, Collection $projects): array
    {
        return array_filter([
            '@type' => 'Service',
            '@id' => route('services.show', $service->slug).'#service',
            'name' => $service->title,
            'serviceType' => $service->title,
            'description' => $service->intro,
            'url' => route('services.show', $service->slug),
            'provider' => ['@id' => self::id('business')],
            'areaServed' => ['@type' => 'Place', 'name' => 'Worldwide'],
            'availableLanguage' => ['English', 'Arabic'],
            'isRelatedTo' => $projects->map(fn (PublicProject $p) => ['@id' => $p->url().'#work'])->values()->all() ?: null,
        ]);
    }

    /**
     * @param  iterable<Faq>  $faqs
     * @return array<string, mixed>|null
     */
    public function faqPage(iterable $faqs, string $canonical): ?array
    {
        $entities = collect($faqs)->map(fn (Faq $faq) => [
            '@type' => 'Question',
            'name' => $faq->question,
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq->answer],
        ])->values()->all();

        return $entities ? ['@type' => 'FAQPage', '@id' => $canonical.'#faq', 'mainEntity' => $entities] : null;
    }

    /**
     * @param  Collection<int, PublicProject>  $projects
     * @return array<string, mixed>
     */
    public function itemList(Collection $projects, string $canonical): array
    {
        return [
            '@type' => 'ItemList',
            '@id' => $canonical.'#list',
            'itemListElement' => $projects->values()->map(fn (PublicProject $p, int $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'url' => $p->url(),
                'name' => $p->title(),
            ])->all(),
        ];
    }
}
