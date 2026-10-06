<?php

namespace App\Models;

use App\Enums\ServiceItemKind;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class ServiceItem extends Model
{
    use HasTranslations;

    /** @var list<string> */
    public array $translatable = ['title', 'body'];

    protected $fillable = ['service_id', 'kind', 'title', 'body', 'sort_order'];

    protected function casts(): array
    {
        return ['kind' => ServiceItemKind::class];
    }

    /** @return BelongsTo<Service, $this> */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
