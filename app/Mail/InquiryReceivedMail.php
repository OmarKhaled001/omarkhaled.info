<?php

namespace App\Mail;

use App\Models\ContactSubmission;
use App\Support\Profile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Polite acknowledgement in the sender's language. Deliberately echoes nothing the sender typed
 * except their first name, so it cannot be abused to relay content to a third party.
 */
class InquiryReceivedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public ContactSubmission $submission) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('contact.autoreply.subject'), tags: ['autoreply']);
    }

    public function content(): Content
    {
        $profile = app(Profile::class);

        return new Content(
            view: 'mail.inquiry-received',
            text: 'mail.inquiry-received-text',
            with: [
                'firstName' => mb_substr(strtok(trim($this->submission->name), ' ') ?: '', 0, 40),
                'hours' => $profile->responseTimeHours(),
                'signature' => $profile->name(),
                'workUrl' => route('projects.index', ['locale' => $this->submission->locale]),
                'dir' => $this->submission->locale === 'ar' ? 'rtl' : 'ltr',
            ],
        );
    }
}
