<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Experience extends Model
{
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['role', 'description'];

    /** Mirrors the column defaults so new instances are complete under strict mode. */
    protected $attributes = [
        'is_current' => false,
        'is_published' => false,
        'sort_order' => 0,
    ];

    protected $fillable = ['company', 'role', 'description', 'started_on', 'ended_on', 'is_current', 'is_published', 'sort_order'];

    protected function casts(): array
    {
        return [
            'started_on' => 'date',
            'ended_on' => 'date',
            'is_current' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @param Builder<self> $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)->orderBy('sort_order');
    }
}
