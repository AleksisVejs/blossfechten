<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends VerifyEmail
{
    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes((int) config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    public function toMail($notifiable): MailMessage
    {
        $apiUrl = $this->verificationUrl($notifiable);
        $frontend = rtrim((string) config('app.frontend_url', ''), '/');
        if ($frontend === '') {
            $frontend = rtrim((string) config('app.url', ''), '/');
        }
        $url = $frontend . '/verify-email?verify_url=' . urlencode($apiUrl);
        $minutes = (int) config('auth.verification.expire', 60);

        return (new MailMessage)
            ->subject('Apstipriniet savu e-pasta adresi — Blossfechten Riga')
            ->view(
                ['emails.verify-email', 'emails.verify-email-text'],
                [
                    'recipientName' => $notifiable->name ?? '',
                    'verifyUrl' => $url,
                    'minutes' => $minutes,
                ]
            );
    }
}
