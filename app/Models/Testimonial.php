<?php

namespace App\Models;

use App\Models\Concerns\HasOptimizedImages;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Testimonial extends Model implements HasMedia
{
    use HasOptimizedImages, InteractsWithMedia {
        HasOptimizedImages::registerMediaConversions insteadof InteractsWithMedia;
    }
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['author_role', 'company', 'quote'];

    /** Mirrors the column defaults so new instances are complete under strict mode. */
    protected $attributes = [
        'is_placeholder' => true,
        'is_published' => false,
        'sort_order' => 0,
    ];

    protected $fillable = ['project_id', 'author_name', 'author_role', 'company', 'quote', 'is_placeholder', 'is_published', 'sort_order'];

    protected function casts(): array
    {
        return ['is_placeholder' => 'boolean', 'is_published' => 'boolean', 'sort_order' => 'integer'];
    }

    protected static function booted(): void
    {
        // A placeholder can never go live, whatever path saves it.
        static::saving(function (self $testimonial): void {
            if ($testimonial->is_placeholder) {
                $testimonial->is_published = false;
            }
        });
    }

    /**
     * Only real, published testimonials may ever be shown publicly.
     *
     * @param  Builder<self>  $query
     */
    public function scopeVisible(Builder $query): void
    {
        $query->where('is_published', true)->where('is_placeholder', false)->orderBy('sort_order');
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile()->useDisk('public');
    }
}
