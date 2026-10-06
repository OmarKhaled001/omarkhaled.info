<?php

namespace App\Mail;

use App\Models\ContactSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Notification to Omar. Reply-To is the sender, so replying from the inbox just works. */
class NewInquiryMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public ContactSubmission $submission) {}

    public function envelope(): Envelope
    {
        $type = __("contact.project_types.{$this->submission->project_type}", [], 'en');

        return new Envelope(
            replyTo: [new Address($this->submission->email, $this->submission->name)],
            subject: "New inquiry: {$type} — {$this->submission->name}",
            tags: ['inquiry'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.new-inquiry',
            text: 'mail.new-inquiry-text',
            with: [
                'type' => __("contact.project_types.{$this->submission->project_type}", [], 'en'),
                'budget' => __("contact.budgets.{$this->submission->budget_range}", [], 'en'),
                'adminUrl' => url(config('portfolio.admin_path').'/contact-submissions/'.$this->submission->id),
            ],
        );
    }
}
