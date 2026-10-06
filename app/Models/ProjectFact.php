<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

/** A verified, citable number about a project (source kept in source_note, admin-only). */
class ProjectFact extends Model
{
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['label'];

    protected $fillable = ['project_id', 'label', 'value', 'source_note', 'sort_order'];

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
