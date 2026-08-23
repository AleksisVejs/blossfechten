<?php

namespace App\Console\Commands;

use App\Models\TrainingSession;
use App\Models\User;
use App\Notifications\EventChangedNotification;
use App\Notifications\EventReminderNotification;
use App\Notifications\NewEventNotification;
use Illuminate\Console\Command;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Facades\Notification;

class SendTestAnnouncement extends Command
{
    private const TYPES = ['announcement', 'reminder', 'changed', 'cancelled'];

    protected $signature = 'events:test-announcement
                            {email : Where to send the sample mail}
                            {--type=announcement : announcement, reminder, changed or cancelled}
                            {--locale= : Preview a specific language instead of the member\'s own}';

    protected $description = 'Send a sample event mail to one address, without touching the calendar or the member list';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $type = (string) $this->option('type');

        if (! in_array($type, self::TYPES, true)) {
            $this->error("Unknown --type '{$type}'. Use one of: " . implode(', ', self::TYPES) . '.');

            return self::FAILURE;
        }

        $session = $this->sampleSession($type);
        $user = User::where('email', $email)->first();
        $locale = (string) ($this->option('locale') ?: '');

        if ($user !== null) {
            // Not saved — only changes which language this one preview renders in.
            if ($locale !== '') {
                $user->locale = $locale;
            }

            $notifiable = $user;
            $this->line("Recipient: {$user->name} <{$email}>, rendering in '{$user->locale}'.");

            if ($type === 'announcement') {
                $this->line('Unsubscribe link will carry their real token, so clicking it does opt them out.');
            }
        } else {
            $notifiable = Notification::route('mail', $email);
            $this->warn("No member found with {$email} — sending to the bare address.");

            if ($type === 'announcement') {
                $this->warn('The unsubscribe link will be inert, since there is no member to unsubscribe.');
            }
        }

        // Sent immediately, not queued: the sample session is never persisted,
        // and queued notifications restore their models by id.
        $notifiable->notifyNow($this->notificationFor($type, $session));

        $this->info("Sent the '{$type}' sample to {$email}. Nothing was added to the calendar, "
            . 'no real event was marked as sent, and no other member was mailed.');

        return self::SUCCESS;
    }

    private function notificationFor(string $type, TrainingSession $session): BaseNotification
    {
        return match ($type) {
            'reminder' => new EventReminderNotification($session),
            'changed' => new EventChangedNotification($session, $this->sampleChanges()),
            'cancelled' => new EventChangedNotification($session, [['field' => 'cancelled']]),
            default => new NewEventNotification($session),
        };
    }

    /**
     * A realistic-looking diff, so the preview shows the strikethrough
     * before-and-after rows exactly as a real edit would.
     *
     * @return array<int, array<string, mixed>>
     */
    private function sampleChanges(): array
    {
        return [
            [
                'field' => 'when',
                'from' => now()->addDays(14)->setTime(11, 0)->format('d.m.Y H:i') . '-14:00',
                'to' => now()->addDays(15)->setTime(10, 0)->format('d.m.Y H:i') . '-13:00',
            ],
            [
                'field' => 'location',
                'from' => 'Ādmiņu iela 4, Rīga',
                'to' => 'Sporta halle, Jelgava',
            ],
        ];
    }

    /** Never persisted: the calendar stays clean and no real session is touched. */
    private function sampleSession(string $type): TrainingSession
    {
        $note = [
            'lv' => 'Šis ir tikai testa e-pasts, lai pārbaudītu paziņojumu sistēmu. Šāds pasākums nenotiks.',
            'en' => 'This is only a test email to check the notification system. No such event is planned.',
            'ru' => 'Это тестовое письмо для проверки системы уведомлений. Такого мероприятия не будет.',
            'cs' => 'Toto je pouze testovací e-mail pro ověření systému oznámení. Žádná taková akce se nekoná.',
            'de' => 'Dies ist nur eine Test-E-Mail zur Prüfung des Benachrichtigungssystems. Eine solche Veranstaltung ist nicht geplant.',
        ];

        // A reminder is about tomorrow; the others are about a future event.
        $start = $type === 'reminder'
            ? now()->addDay()->setTime(11, 0)
            : now()->addDays(14)->setTime(11, 0);

        return new TrainingSession([
            'starts_at' => $start,
            'ends_at' => (clone $start)->addHours(3),
            'location' => 'Ādmiņu iela 4, Rīga',
            'focus' => 'Longsword',
            'title' => [
                'lv' => '[TESTS] Vasaras nometne',
                'en' => '[TEST] Summer camp',
                'ru' => '[ТЕСТ] Летний лагерь',
                'cs' => '[TEST] Letní tábor',
                'de' => '[TEST] Sommerlager',
            ],
            'description' => $note,
            'capacity' => 20,
        ]);
    }
}
