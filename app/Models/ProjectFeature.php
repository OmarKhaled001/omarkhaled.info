<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class ProjectFeature extends Model
{
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['title', 'body'];

    protected $fillable = ['project_id', 'title', 'body', 'sort_order'];

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
