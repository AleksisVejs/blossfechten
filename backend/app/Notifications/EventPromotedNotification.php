<?php

namespace App\Notifications;

use App\Models\TrainingSession;
use App\Notifications\Concerns\FormatsEventMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent when a seat comes free and the member at the head of the waiting list
 * is moved up to a confirmed place.
 *
 * Like the reminder and the change notice this is transactional — it follows
 * from the member's own registration rather than the announcement
 * subscription — so it carries no unsubscribe link and ignores the
 * notify_new_events preference. The way out is to give the seat back.
 *
 * It is also the one mail a waitlisted member genuinely cannot do without: a
 * seat they were never told about is a seat nobody turns up for.
 */
class EventPromotedNotification extends Notification implements ShouldQueue
{
    use FormatsEventMail, Queueable;

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

        $title = $this->localized($this->session->title, $locale)
            ?: ($this->session->focus ?: trans('messages.emails.promoted.fallback_title', [], $locale));

        return (new MailMessage)
            ->subject(trans('messages.emails.promoted.subject', ['title' => $title], $locale))
            ->view(
                ['emails.event-promoted', 'emails.event-promoted-text'],
                [
                    'recipientName' => $notifiable->name ?? '',
                    'locale' => $locale,
                    'eventTitle' => $title,
                    'eventDescription' => $this->localized($this->session->description, $locale),
                    'eventWhen' => $this->formatWhen($this->session->starts_at, $this->session->ends_at),
                    'eventLocation' => $this->session->location,
                    'eventUrl' => $this->frontendUrl() . '/dashboard',
                ]
            );
    }
}
