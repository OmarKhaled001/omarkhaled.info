<?php

namespace App\Actions;

use App\Enums\SubmissionStatus;
use App\Mail\InquiryReceivedMail;
use App\Mail\NewInquiryMail;
use App\Models\ContactSubmission;
use App\Support\Profile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Stores a validated inquiry and queues both emails. Spam is stored (for review) but never emailed.
 */
final readonly class SubmitInquiry
{
    public function __construct(private Profile $profile) {}

    /**
     * @param  array{name: string, email: string, company?: string|null, project_type: string, budget_range: string, message: string}  $data
     */
    public function handle(array $data, string $locale, ?string $ip, ?string $userAgent, ?string $spamReason = null): ContactSubmission
    {
        $submission = ContactSubmission::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'company' => $data['company'] ?? null,
            'project_type' => $data['project_type'],
            'budget_range' => $data['budget_range'],
            'message' => $data['message'],
            'locale' => $locale,
            'ip_hash' => $ip ? hash_hmac('sha256', $ip, (string) config('app.key')) : null,
            'user_agent' => $userAgent ? mb_substr($userAgent, 0, 255) : null,
            'status' => $spamReason ? SubmissionStatus::Spam : SubmissionStatus::New,
            'spam_reason' => $spamReason,
        ]);

        if ($spamReason) {
            return $submission;
        }

        $recipient = $this->profile->mailRecipient();

        if ($recipient) {
            Mail::to($recipient)->queue(new NewInquiryMail($submission));
        } else {
            Log::warning('Contact inquiry stored but not emailed: no recipient configured.', ['id' => $submission->id]);
        }

        // One acknowledgement per address per day, so the form can't be used to mail-bomb someone.
        $key = 'contact:autoreply:'.sha1(mb_strtolower($submission->email));
        if (! RateLimiter::tooManyAttempts($key, 1)) {
            RateLimiter::hit($key, 86400);
            Mail::to($submission->email)->locale($locale)->queue(new InquiryReceivedMail($submission));
        }

        return $submission;
    }
}
