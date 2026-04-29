<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable)
    {
        $frontend = rtrim((string) config('app.frontend_url', ''), '/');
        if ($frontend === '') {
            $frontend = rtrim((string) config('app.url', ''), '/');
        }
        $url = $frontend . '/reset-password?token=' . $this->token
            . '&email=' . urlencode($notifiable->getEmailForPasswordReset());

        $minutes = (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        return (new MailMessage)
            ->subject('Paroles atjaunošana — Blossfechten Riga')
            ->view(
                ['emails.reset-password', 'emails.reset-password-text'],
                [
                    'recipientName' => $notifiable->name ?? '',
                    'resetUrl' => $url,
                    'minutes' => $minutes,
                ]
            );
    }
}
