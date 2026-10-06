<?php

namespace App\Models;

use App\Enums\TechnologyDomain;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Technology extends Model
{
    /** Mirrors the column defaults so new instances are complete under strict mode. */
    protected $attributes = [
        'show_on_about' => true,
        'show_in_stack' => false,
        'sort_order' => 0,
    ];

    protected $fillable = ['slug', 'name', 'domain', 'icon', 'url', 'show_on_about', 'show_in_stack', 'sort_order'];

    protected function casts(): array
    {
        return [
            'domain' => TechnologyDomain::class,
            'show_on_about' => 'boolean',
            'show_in_stack' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsToMany<Project, $this> */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class);
    }
}
