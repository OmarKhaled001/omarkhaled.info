<?php

namespace App\Models;

use App\Enums\EngagementType;
use App\Enums\SchemaType;
use App\Models\Concerns\HasOptimizedImages;
use App\Observers\ProjectObserver;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

/**
 * Raw case-study record. Public output must go through App\Presenters\PublicProject,
 * which applies the anonymity toggles.
 *
 * @property EngagementType $engagement_type
 * @property SchemaType $schema_type
 * @property string $slug
 * @property int|null $year
 * @property string|null $live_url
 * @property string|null $repo_url
 * @property bool $show_client_name
 * @property bool $show_live_link
 * @property bool $show_logo
 * @property bool $show_screenshots
 * @property bool $show_repo_link
 * @property bool $is_published
 * @property bool $is_featured
 * @property list<string>|null $client_aliases
 */
#[ObservedBy(ProjectObserver::class)]
class Project extends Model implements HasMedia
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    use HasOptimizedImages, InteractsWithMedia {
        HasOptimizedImages::registerMediaConversions insteadof InteractsWithMedia;
    }
    use HasTranslations;

    /** Collections that can identify the client and live on the private disk until revealed. */
    public const array IDENTIFIABLE_COLLECTIONS = ['logo' => 'show_logo', 'screenshots' => 'show_screenshots'];

    /** @var list<string> */
    public array $translatable = [
        'title', 'anonymized_title', 'summary', 'anonymized_summary', 'client_name',
        'industry', 'role', 'challenge', 'solution', 'architecture', 'results',
        'meta_title', 'meta_description',
    ];

    /** Mirrors the column defaults so new instances are complete under strict mode. */
    protected $attributes = [
        'engagement_type' => 'client',
        'schema_type' => 'CreativeWork',
        'show_client_name' => false,
        'show_live_link' => false,
        'show_logo' => false,
        'show_screenshots' => false,
        'show_repo_link' => false,
        'is_published' => false,
        'is_featured' => false,
        'sort_order' => 0,
    ];

    protected $fillable = [
        'slug', 'title', 'anonymized_title', 'summary', 'anonymized_summary', 'client_name', 'client_aliases',
        'industry', 'role', 'challenge', 'solution', 'architecture', 'results',
        'engagement_type', 'schema_type', 'live_url', 'repo_url', 'year',
        'show_client_name', 'show_live_link', 'show_logo', 'show_screenshots', 'show_repo_link',
        'is_published', 'is_featured', 'sort_order', 'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'client_aliases' => 'array',
            'engagement_type' => EngagementType::class,
            'schema_type' => SchemaType::class,
            'year' => 'integer',
            'show_client_name' => 'boolean',
            'show_live_link' => 'boolean',
            'show_logo' => 'boolean',
            'show_screenshots' => 'boolean',
            'show_repo_link' => 'boolean',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @param Builder<self> $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /** @param Builder<self> $query */
    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /** @param Builder<self> $query */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    /** @return HasMany<ProjectFeature, $this> */
    public function features(): HasMany
    {
        return $this->hasMany(ProjectFeature::class)->orderBy('sort_order');
    }

    /** @return HasMany<ProjectFact, $this> */
    public function facts(): HasMany
    {
        return $this->hasMany(ProjectFact::class)->orderBy('sort_order');
    }

    /** @return BelongsToMany<Category, $this> */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->orderBy('sort_order');
    }

    /** @return BelongsToMany<Technology, $this> */
    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class)
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    /** @return BelongsToMany<Service, $this> */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class)
            ->withPivot('sort_order')
            ->orderBy('services.sort_order');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile()->useDisk('media_private');
        $this->addMediaCollection('screenshots')->useDisk('media_private');
        $this->addMediaCollection('cover')->singleFile()->useDisk('public');
        $this->addMediaCollection('architecture')->singleFile()->useDisk('public');
    }
}
