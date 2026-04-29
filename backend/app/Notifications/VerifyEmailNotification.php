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
        $frontend = rtrim((string) env('FRONTEND_URL', ''), '/');
        if ($frontend === '') {
            $frontend = rtrim((string) config('app.url', ''), '/');
        }
        $url = $frontend . '/verify-email?verify_url=' . urlencode($apiUrl);

        return (new MailMessage)
            ->subject('Apstipriniet savu e-pasta adresi — Blossfechten Riga')
            ->greeting('Sveiki, ' . ($notifiable->name ?? '') . '!')
            ->line('Paldies, ka pievienojāties Blossfechten Riga skolai. Lūdzu, apstipriniet savu e-pasta adresi, noklikšķinot uz pogas zemāk.')
            ->action('Apstiprināt e-pastu', $url)
            ->line('Šī saite ir derīga ' . (int) config('auth.verification.expire', 60) . ' minūtes.')
            ->line('Ja poga neatveras, ielīmējiet šo saiti pārlūkā: ' . $url)
            ->line('Ja jūs neveidojāt kontu, varat ignorēt šo e-pastu.');
    }
}
