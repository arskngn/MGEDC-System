<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * SendNotificationMail
 * 
 * Queued mailable for sending notification emails with automatic retry support.
 * This allows email sending to be handled asynchronously and retried on failure.
 */
class SendNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Number of times to attempt delivery
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Number of seconds before re-attempting a failed job
     *
     * @var int
     */
    public $backoff = [3600, 7200]; // 1 hour, then 2 hours

    /**
     * Create a new message instance.
     */
    public function __construct(
        protected string $recipientEmail,
        protected string $subject,
        protected string $body,
        protected string $fromEmail,
        protected string $fromName
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope()
    {
        return [
            'from' => [$this->fromEmail => $this->fromName],
            'to' => [$this->recipientEmail],
            'subject' => $this->subject,
        ];
    }

    /**
     * Get the message content definition.
     */
    public function content()
    {
        return [
            'view' => 'emails.notification',
            'with' => ['body' => $this->body],
        ];
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments()
    {
        return [];
    }

    /**
     * Handle a failed job attempt.
     * 
     * This is called when the job fails after all retries are exhausted.
     */
    public function failed(\Throwable $exception): void
    {
        \Illuminate\Support\Facades\Log::error(
            "Failed to send notification email to {$this->recipientEmail} after " . 
            ($this->tries ?? 1) . " attempts: " . $exception->getMessage()
        );
    }
}
