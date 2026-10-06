<?php

namespace App\Support\Seo;

use App\Support\Locales;

/**
 * Per-page metadata rendered by <x-seo>. Built by controllers; the JSON-LD graph is attached later by SchemaGraph.
 */
final class Seo
{
    /** @var array<string, string> locale => absolute URL */
    public array $alternates = [];

    /** @var list<array<string, mixed>> */
    public array $schema = [];

    public ?string $image = null;

    public ?string $imageAlt = null;

    public string $type = 'website';

    /** schema.org WebPage subtype for this page. */
    public string $pageType = 'WebPage';

    public bool $isHome = false;

    /** Eyebrow line on the generated OG card. */
    public ?string $ogEyebrow = null;

    /** @var list<array{0: string, 1: string}> [name, absolute url] */
    public array $breadcrumbs = [];

    public bool $index = true;

    public function __construct(
        public string $title,
        public string $description,
        public string $canonical,
    ) {}

    /**
     * Metadata for the current named route, with alternates for every locale.
     *
     * @param  array<string, mixed>  $parameters  route parameters other than locale
     */
    public static function forRoute(string $title, string $description, string $routeName, array $parameters = []): self
    {
        $seo = new self($title, $description, route($routeName, $parameters));

        foreach (Locales::SUPPORTED as $locale) {
            $seo->alternates[$locale] = route($routeName, array_merge($parameters, ['locale' => $locale]));
        }

        return $seo;
    }

    public function noindex(): self
    {
        $this->index = false;

        return $this;
    }

    /**
     * @param  list<array{0: string, 1: string}>  $items
     */
    public function withBreadcrumbs(array $items): self
    {
        $this->breadcrumbs = $items;

        return $this;
    }

    public function asPage(string $type, ?string $ogEyebrow = null): self
    {
        $this->pageType = $type;
        $this->ogEyebrow = $ogEyebrow;

        return $this;
    }

    public function withImage(?string $url, ?string $alt = null): self
    {
        $this->image = $url;
        $this->imageAlt = $alt;

        return $this;
    }

    /** @param array<string, mixed>|null $node */
    public function addSchema(?array $node): self
    {
        if ($node !== null) {
            $this->schema[] = $node;
        }

        return $this;
    }

    public function fullTitle(): string
    {
        $suffix = __('site.title_suffix');

        return str_contains($this->title, $suffix) ? $this->title : "{$this->title} — {$suffix}";
    }

    public function xDefault(): ?string
    {
        return $this->alternates[Locales::DEFAULT] ?? null;
    }
}
