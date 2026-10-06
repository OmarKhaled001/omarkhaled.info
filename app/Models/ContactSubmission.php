<?php

namespace App\Models;

use App\Enums\SubmissionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property SubmissionStatus $status
 * @property string $email
 * @property string $locale
 */
class ContactSubmission extends Model
{
    use MassPrunable;

    public const int RETENTION_MONTHS = 24;

    public const int SPAM_RETENTION_DAYS = 30;

    protected $fillable = [
        'name', 'email', 'company', 'project_type', 'budget_range', 'message',
        'locale', 'ip_hash', 'user_agent', 'status', 'spam_reason',
    ];

    protected function casts(): array
    {
        return ['status' => SubmissionStatus::class];
    }

    /**
     * Retention policy stated in the privacy policy.
     *
     * @return Builder<self>
     */
    public function prunable(): Builder
    {
        return self::query()
            ->where(fn (Builder $q) => $q
                ->where('status', SubmissionStatus::Spam)
                ->where('created_at', '<', now()->subDays(self::SPAM_RETENTION_DAYS)))
            ->orWhere('created_at', '<', now()->subMonths(self::RETENTION_MONTHS));
    }
}
