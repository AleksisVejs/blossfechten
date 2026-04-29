<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable)
    {
        $frontend = rtrim((string) config('app.frontend_url', ''), '/');
        // Fallback: avoid empty/relative redirects if FRONTEND_URL isn't configured.
        if ($frontend === '') {
            $frontend = rtrim((string) config('app.url', ''), '/');
        }
        $url = $frontend . '/reset-password?token=' . $this->token
            . '&email=' . urlencode($notifiable->getEmailForPasswordReset());

        $minutes = (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        return (new MailMessage)
            ->subject('Paroles atjaunošana — Blossfechten Riga')
            ->greeting('Sveiki, ' . ($notifiable->name ?? '') . '!')
            ->line('Saņemts pieprasījums atjaunot paroli jūsu kontam.')
            ->action('Atjaunot paroli', $url)
            ->line('Šī saite ir derīga ' . $minutes . ' minūtes.')
            ->line('Ja jūs nepieprasījāt paroles atjaunošanu, varat ignorēt šo e-pastu.');
    }
}
