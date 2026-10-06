<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/** Editable long-form pages identified by key (about, privacy). */
class Page extends Model
{
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['title', 'body', 'meta_title', 'meta_description'];

    protected $fillable = ['key', 'title', 'body', 'meta_title', 'meta_description'];

    public static function byKey(string $key): ?self
    {
        return static::query()->where('key', $key)->first();
    }
}
