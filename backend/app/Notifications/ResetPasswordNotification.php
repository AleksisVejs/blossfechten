<?php

namespace App\Notifications;

use App\Mail\ResetPasswordConfirmation;
use Illuminate\Auth\Notifications\ResetPassword;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable)
    {
        $frontend = rtrim((string) env('FRONTEND_URL', ''), '/');
        // Fallback: avoid empty/relative redirects if FRONTEND_URL isn't configured.
        if ($frontend === '') {
            $frontend = rtrim((string) config('app.url', ''), '/');
        }
        $url = $frontend . '/reset-password?token=' . $this->token
            . '&email=' . urlencode($notifiable->getEmailForPasswordReset());

        $minutes = (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        return new ResetPasswordConfirmation(
            recipientName: (string) ($notifiable->name ?? ''),
            resetUrl: $url,
            minutes: $minutes,
        );
    }
}
