<?php

namespace App\Notifications;

use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Symfony\Component\Mime\Email;

class NewEventNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** Locales the site ships translations for. */
    private const LOCALES = ['lv', 'en', 'ru', 'cs', 'de'];

    /**
     * Order to fall back through when an admin leaves a language blank.
     * English leads: a member reading in German is likelier to follow an
     * English title than a Latvian one.
     */
    private const CONTENT_FALLBACKS = ['en', 'lv', 'ru', 'cs', 'de'];

    /**
     * If the admin deletes the event before the queue drains, silently drop the
     * pending mail rather than piling up failed jobs — not sending is exactly
     * what deleting the event should mean.
     */
    public bool $deleteWhenMissingModels = true;

    public function __construct(public TrainingSession $session)
    {
        $this->onQueue('mail');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = $this->localeFor($notifiable);
        $unsubscribeUrl = $this->unsubscribeUrl($notifiable);

        $title = $this->localized($this->session->title, $locale)
            ?: ($this->session->focus ?: trans('messages.emails.new_event.fallback_title', [], $locale));

        return (new MailMessage)
            ->subject(trans('messages.emails.new_event.subject', ['title' => $title], $locale))
            ->view(
                ['emails.new-event', 'emails.new-event-text'],
                [
                    'recipientName' => $notifiable->name ?? '',
                    'locale' => $locale,
                    'eventTitle' => $title,
                    'eventDescription' => $this->localized($this->session->description, $locale),
                    'eventWhen' => $this->formatWhen(),
                    'eventLocation' => $this->session->location,
                    'eventUrl' => $this->frontendUrl() . '/schedule',
                    'unsubscribeUrl' => $unsubscribeUrl,
                ]
            )
            // One-click unsubscribe. Gmail and Yahoo both weigh this for bulk
            // senders, and it keeps complaints from turning into spam reports.
            // Skipped when there is no token to unsubscribe (an on-demand test
            // address): a malformed header is worse than no header at all.
            ->withSymfonyMessage(function (Email $message) use ($unsubscribeUrl) {
                if (! str_ends_with($unsubscribeUrl, 'token=')) {
                    $headers = $message->getHeaders();
                    $headers->addTextHeader('List-Unsubscribe', '<' . $unsubscribeUrl . '>');
                    $headers->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
                }
            });
    }

    private function localeFor(object $notifiable): string
    {
        $locale = $notifiable->locale ?? null;

        return in_array($locale, self::LOCALES, true) ? $locale : 'lv';
    }

    /**
     * Per-entity translations are json columns keyed by locale, and admins do
     * not always fill every language — fall back rather than mail a blank line.
     */
    private function localized(?array $values, string $locale): ?string
    {
        if (empty($values)) {
            return null;
        }

        foreach ([$locale, ...self::CONTENT_FALLBACKS] as $candidate) {
            $value = trim((string) ($values[$candidate] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function formatWhen(): string
    {
        $start = $this->session->starts_at;
        $end = $this->session->ends_at;

        if ($start === null) {
            return '';
        }

        if ($end === null) {
            return $start->format('d.m.Y H:i');
        }

        return $start->isSameDay($end)
            ? $start->format('d.m.Y H:i') . '-' . $end->format('H:i')
            : $start->format('d.m.Y H:i') . ' - ' . $end->format('d.m.Y H:i');
    }

    private function unsubscribeUrl(object $notifiable): string
    {
        $token = $notifiable instanceof User
            ? $notifiable->ensureUnsubscribeToken()
            : (string) ($notifiable->unsubscribe_token ?? '');

        return $this->frontendUrl() . '/unsubscribe?token=' . urlencode($token);
    }

    private function frontendUrl(): string
    {
        $frontend = rtrim((string) config('app.frontend_url', ''), '/');

        return $frontend !== '' ? $frontend : rtrim((string) config('app.url', ''), '/');
    }
}
