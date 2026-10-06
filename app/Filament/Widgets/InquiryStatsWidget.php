<?php

namespace App\Filament\Widgets;

use App\Enums\SubmissionStatus;
use App\Models\ContactSubmission;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InquiryStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -5;

    protected function getStats(): array
    {
        return [
            Stat::make('New inquiries', ContactSubmission::query()->where('status', SubmissionStatus::New)->count()),
            Stat::make('Inquiries this week', ContactSubmission::query()->where('status', '!=', SubmissionStatus::Spam)->where('created_at', '>=', now()->subWeek())->count()),
            Stat::make('Published projects', Project::query()->published()->count())
                ->description(Project::query()->published()->where('show_client_name', false)->count().' anonymized'),
        ];
    }
}
