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

    public function withImage(?string $url, ?string $alt = null): self
    {
        $this->image = $url;
        $this->imageAlt = $alt;

        return $this;
    }

    /** @param array<string, mixed> $node */
    public function addSchema(array $node): self
    {
        $this->schema[] = $node;

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
