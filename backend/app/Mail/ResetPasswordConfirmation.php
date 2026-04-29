<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $resetUrl,
        public int $minutes,
    ) {}

    public function envelope(): Envelope
    {
        $fromAddress = (string) config('mail.from.address');
        $fromName = (string) config('mail.from.name');

        return new Envelope(
            subject: 'Paroles atjaunošana — Blossfechten Riga',
            from: new Address($fromAddress, $fromName),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reset-password',
            text: 'emails.reset-password-text',
            with: [
                'recipientName' => $this->recipientName,
                'resetUrl' => $this->resetUrl,
                'minutes' => $this->minutes,
            ],
        );
    }
}

