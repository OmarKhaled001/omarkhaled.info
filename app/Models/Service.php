<?php

namespace App\Models;

use App\Enums\ServiceItemKind;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Translatable\HasTranslations;

class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['title', 'card_summary', 'headline', 'intro', 'problem', 'cta_text', 'meta_title', 'meta_description'];

    /** Mirrors the column defaults so new instances are complete under strict mode. */
    protected $attributes = [
        'is_published' => false,
        'sort_order' => 0,
    ];

    protected $fillable = [
        'slug', 'icon', 'title', 'card_summary', 'headline', 'intro', 'problem', 'cta_text',
        'meta_title', 'meta_description', 'is_published', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @param Builder<self> $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /** @param Builder<self> $query */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    /** @return HasMany<ServiceItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(ServiceItem::class)->orderBy('sort_order');
    }

    /** @return HasMany<ServiceItem, $this> */
    public function deliverables(): HasMany
    {
        return $this->items()->where('kind', ServiceItemKind::Deliverable);
    }

    /** @return HasMany<ServiceItem, $this> */
    public function processSteps(): HasMany
    {
        return $this->items()->where('kind', ServiceItemKind::ProcessStep);
    }

    /** @return MorphMany<Faq, $this> */
    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable')->orderBy('sort_order');
    }

    /** @return BelongsToMany<Project, $this> */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }
}
