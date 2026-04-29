<?php

namespace App\Notifications;

use App\Mail\ResetPasswordConfirmation;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\View;

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

        // Defensive fallback: if the styled reset-password templates aren't present
        // on the deployed server, don't crash the whole endpoint.
        if (
            class_exists(ResetPasswordConfirmation::class) &&
            View::exists('emails.reset-password') &&
            View::exists('emails.reset-password-text')
        ) {
            return new ResetPasswordConfirmation(
                recipientName: (string) ($notifiable->name ?? ''),
                resetUrl: $url,
                minutes: $minutes,
            );
        }

        return (new MailMessage)
            ->subject('Paroles atjaunošana — Blossfechten Riga')
            ->greeting('Sveiki, ' . ($notifiable->name ?? '') . '!')
            ->line('Saņemts pieprasījums atjaunot paroli jūsu kontam.')
            ->action('Atjaunot paroli', $url)
            ->line('Šī saite ir derīga ' . $minutes . ' minūtes.')
            ->line('Ja jūs nepieprasījāt paroles atjaunošanu, varat ignorēt šo e-pastu.');
    }
}
