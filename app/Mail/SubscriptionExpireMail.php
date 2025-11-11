<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpireMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $expiresAt;
    public $fullname;
    
    /**
     * Create a new message instance.
     */
    public function __construct($user, $expiresAt = null)
    {
        $this->user = $user;
        $this->fullname = $user?->fullname ?? ($user['fullname'] ?? null);
        $this->expiresAt = $expiresAt;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Subscription Expire Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription.success',
            with: [
                'user' => $this->user,
                'fullname' => $this->fullname ?? null,
                'expires_at' => $this->expires_at ?? now()->format('Y-m-d H:i:s'),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

}
