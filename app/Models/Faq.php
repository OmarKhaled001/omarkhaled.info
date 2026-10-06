<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Translatable\HasTranslations;

class Faq extends Model
{
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['question', 'answer'];

    /** Mirrors the column defaults so new instances are complete under strict mode. */
    protected $attributes = [
        'is_published' => true,
        'sort_order' => 0,
    ];

    protected $fillable = ['faqable_type', 'faqable_id', 'question', 'answer', 'is_published', 'sort_order'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'sort_order' => 'integer'];
    }

    /** @return MorphTo<Model, $this> */
    public function faqable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @param Builder<self> $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /**
     * General FAQs shown on the home page (not attached to a service).
     *
     * @param  Builder<self>  $query
     */
    public function scopeGeneral(Builder $query): void
    {
        $query->whereNull('faqable_type');
    }
}
