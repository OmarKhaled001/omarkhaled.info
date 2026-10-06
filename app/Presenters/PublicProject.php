<?php

namespace App\Presenters;

use App\Enums\SchemaType;
use App\Models\Category;
use App\Models\Project;
use App\Models\ProjectFact;
use App\Models\ProjectFeature;
use App\Models\Service;
use App\Models\Technology;
use App\Support\Html;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * The ONLY way public code may read a project. Applies the per-project visibility toggles,
 * so anonymized projects never expose the client's name, logo, domain or screenshots.
 */
final readonly class PublicProject
{
    public function __construct(public Project $project) {}

    /**
     * @param  iterable<Project>  $projects
     * @return Collection<int, self>
     */
    public static function collection(iterable $projects): Collection
    {
        return collect($projects)->map(fn (Project $p) => new self($p))->values();
    }

    public function key(): int
    {
        return $this->project->getKey();
    }

    public function slug(): string
    {
        return $this->project->slug;
    }

    public function url(?string $locale = null): string
    {
        $parameters = ['slug' => $this->project->slug];

        return route('projects.show', $locale ? $parameters + ['locale' => $locale] : $parameters);
    }

    public function isRevealed(): bool
    {
        return $this->project->show_client_name;
    }

    public function title(?string $locale = null): string
    {
        return $this->field($this->isRevealed() ? 'title' : 'anonymized_title', $locale);
    }

    public function summary(?string $locale = null): string
    {
        return $this->field($this->isRevealed() ? 'summary' : 'anonymized_summary', $locale);
    }

    public function clientName(?string $locale = null): ?string
    {
        return $this->isRevealed() ? ($this->field('client_name', $locale) ?: null) : null;
    }

    public function liveUrl(): ?string
    {
        return $this->project->show_live_link ? Str::sanitizeUrl($this->project->live_url) : null;
    }

    public function liveHost(): ?string
    {
        $url = $this->liveUrl();

        return $url ? preg_replace('/^www\./', '', (string) parse_url($url, PHP_URL_HOST)) : null;
    }

    public function repoUrl(): ?string
    {
        return $this->project->show_repo_link ? Str::sanitizeUrl($this->project->repo_url) : null;
    }

    public function industry(?string $locale = null): ?string
    {
        return $this->field('industry', $locale) ?: null;
    }

    public function role(?string $locale = null): ?string
    {
        return $this->field('role', $locale) ?: null;
    }

    public function year(): ?int
    {
        return $this->project->year;
    }

    public function engagementLabel(): string
    {
        return $this->project->engagement_type->label();
    }

    public function schemaType(): SchemaType
    {
        return $this->project->schema_type;
    }

    /** Sanitized HTML of a long-form section, or '' when empty. */
    public function section(string $name, ?string $locale = null): string
    {
        abort_unless(in_array($name, ['challenge', 'solution', 'architecture', 'results'], true), 500);

        return Html::clean($this->field($name, $locale));
    }

    public function hasResults(?string $locale = null): bool
    {
        return Html::text($this->section('results', $locale)) !== '';
    }

    public function metaTitle(?string $locale = null): string
    {
        return $this->field('meta_title', $locale) ?: $this->title($locale);
    }

    public function metaDescription(?string $locale = null): string
    {
        return Str::limit($this->field('meta_description', $locale) ?: $this->summary($locale), 160, '…');
    }

    /** @return Collection<int, ProjectFeature> */
    public function features(): Collection
    {
        return $this->project->features;
    }

    /** @return Collection<int, ProjectFact> */
    public function facts(): Collection
    {
        return $this->project->facts;
    }

    /** @return Collection<int, Technology> */
    public function technologies(): Collection
    {
        return $this->project->technologies;
    }

    /** @return Collection<int, Category> */
    public function categories(): Collection
    {
        return $this->project->categories;
    }

    /** @return Collection<int, Service> */
    public function services(): Collection
    {
        return $this->project->services->where('is_published', true)->values();
    }

    public function logo(): ?Media
    {
        return $this->project->show_logo ? $this->publicMedia('logo')->first() : null;
    }

    /** @return Collection<int, Media> */
    public function screenshots(): Collection
    {
        return $this->project->show_screenshots ? $this->publicMedia('screenshots') : collect();
    }

    /** Non-identifiable cover; when null the view renders a generated plate. */
    public function cover(): ?Media
    {
        return $this->publicMedia('cover')->first() ?? $this->screenshots()->first();
    }

    public function architectureImage(): ?Media
    {
        return $this->publicMedia('architecture')->first();
    }

    public function imageAlt(int $position = 1, ?string $locale = null): string
    {
        return __('projects.screenshot_alt', ['n' => $position, 'title' => $this->title($locale)], $locale);
    }

    /**
     * Every piece of text this project can put on a public surface — used by the leak guard.
     *
     * @return list<string>
     */
    public function publicText(): array
    {
        $texts = [];

        foreach (['en', 'ar'] as $locale) {
            $texts[] = $this->title($locale);
            $texts[] = $this->summary($locale);
            $texts[] = $this->metaTitle($locale);
            $texts[] = $this->metaDescription($locale);
            $texts[] = (string) $this->industry($locale);
            $texts[] = (string) $this->role($locale);
            $texts[] = $this->imageAlt(1, $locale);

            foreach (['challenge', 'solution', 'architecture', 'results'] as $section) {
                $texts[] = $this->section($section, $locale);
            }

            foreach ($this->project->features()->get() as $feature) {
                $texts[] = (string) $feature->getTranslation('title', $locale, false);
                $texts[] = (string) $feature->getTranslation('body', $locale, false);
            }

            foreach ($this->project->facts()->get() as $fact) {
                $texts[] = (string) $fact->getTranslation('label', $locale, false);
                $texts[] = $fact->value;
            }
        }

        $texts[] = $this->project->slug;

        return array_values(array_filter($texts, fn (string $t) => $t !== ''));
    }

    /**
     * Media in a collection that is actually web-accessible (never the private disk).
     *
     * @return Collection<int, Media>
     */
    private function publicMedia(string $collection): Collection
    {
        return $this->project->getMedia($collection)
            ->filter(fn (Media $media) => $media->disk === 'public')
            ->values();
    }

    private function field(string $attribute, ?string $locale): string
    {
        return (string) $this->project->getTranslation($attribute, $locale ?? app()->getLocale(), true);
    }
}
