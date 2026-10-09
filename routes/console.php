<?php

use App\Models\ContactSubmission;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
| Tasks run in-process (Schedule::call), not as child processes: Schedule::command() starts a
| new process through proc_open, which shared hosts such as Hostinger disable.
*/

// Retention policy from the privacy page: spam after 30 days, inquiries after 24 months.
Schedule::call(fn () => Artisan::call('model:prune', ['--model' => [ContactSubmission::class]]))
    ->name('prune-contact-submissions')
    ->daily();

// Shared hosting without Supervisor: let the per-minute cron drain the database queue.
if (config('portfolio.queue_via_scheduler')) {
    Schedule::call(fn () => Artisan::call('queue:work', ['--stop-when-empty' => true, '--max-time' => 50, '--tries' => 3]))
        ->name('drain-queue')
        ->everyMinute()
        ->withoutOverlapping();
}
