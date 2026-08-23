<?php

namespace App\Console\Commands;

use App\Models\TrainingSession;
use App\Models\User;
use App\Notifications\NewEventNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendTestAnnouncement extends Command
{
    protected $signature = 'events:test-announcement
                            {email : Where to send the sample announcement}
                            {--locale= : Preview a specific language instead of the member\'s own}';

    protected $description = 'Send a sample new-event announcement to one address, without touching the calendar or the member list';

    public function handle(): int
    {
        $email = (string) $this->argument('email');

        // Never persisted: the calendar stays clean and no real session is
        // ever marked as announced by this command.
        $session = new TrainingSession([
            'starts_at' => now()->addDays(14)->setTime(11, 0),
            'ends_at' => now()->addDays(14)->setTime(14, 0),
            'location' => 'Ādmiņu iela 4, Rīga',
            'focus' => 'Longsword',
            'title' => [
                'lv' => '[TESTS] Vasaras nometne',
                'en' => '[TEST] Summer camp',
                'ru' => '[ТЕСТ] Летний лагерь',
                'cs' => '[TEST] Letní tábor',
                'de' => '[TEST] Sommerlager',
            ],
            'description' => [
                'lv' => 'Šis ir tikai testa e-pasts, lai pārbaudītu paziņojumu sistēmu. Šāds pasākums nenotiks.',
                'en' => 'This is only a test email to check the announcement system. No such event is planned.',
                'ru' => 'Это тестовое письмо для проверки системы уведомлений. Такого мероприятия не будет.',
                'cs' => 'Toto je pouze testovací e-mail pro ověření systému oznámení. Žádná taková akce se nekoná.',
                'de' => 'Dies ist nur eine Test-E-Mail zur Prüfung des Benachrichtigungssystems. Eine solche Veranstaltung ist nicht geplant.',
            ],
            'capacity' => 20,
        ]);

        $user = User::where('email', $email)->first();
        $locale = (string) ($this->option('locale') ?: '');

        if ($user !== null) {
            // Not saved — only changes which language this one preview renders in.
            if ($locale !== '') {
                $user->locale = $locale;
            }

            $notifiable = $user;
            $this->line("Recipient: {$user->name} <{$email}>, rendering in '{$user->locale}'.");
            $this->line('Unsubscribe link will carry their real token, so clicking it does opt them out.');
        } else {
            $notifiable = Notification::route('mail', $email);
            $this->warn("No member found with {$email} — sending to the bare address.");
            $this->warn('The unsubscribe link will be inert, since there is no member to unsubscribe.');
        }

        // Sent immediately, not queued: the sample session is never persisted,
        // and queued notifications restore their models by id.
        $notifiable->notifyNow(new NewEventNotification($session));
        $this->info("Sent to {$email}. Nothing was added to the calendar and no other member was mailed.");

        return self::SUCCESS;
    }
}
