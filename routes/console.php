<?php

use App\Models\ContactSubmission;
use Illuminate\Support\Facades\Schedule;

// Retention policy from the privacy page: spam after 30 days, inquiries after 24 months.
Schedule::command('model:prune', ['--model' => [ContactSubmission::class]])->daily();

// Shared hosting without Supervisor: let the per-minute cron drain the database queue.
if (config('portfolio.queue_via_scheduler')) {
    Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3')
        ->everyMinute()
        ->withoutOverlapping()
        ->runInBackground();
}
