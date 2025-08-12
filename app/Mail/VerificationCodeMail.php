<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code;
    public $email;

    /**
     * Create a new message instance.
     */
    public function __construct(string $code, string $email)
    {
        $this->code = $code;
        $this->email = $email;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        \Log::info("VerificationCodeMail envelope - email: " . $this->email);
        return new Envelope(
            subject: '🌱 Your Nutrifarm Verification Code',
            from: config('mail.from.address', 'noreply@nutrifarm.com'),
            replyTo: [config('mail.from.address', 'noreply@nutrifarm.com')]
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.verification-code',
            text: 'emails.verification-code-text',
            with: [
                'code' => $this->code,
                'email' => $this->email,
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
