<?php

namespace App\Notifications;

use App\Models\TrainingSession;
use App\Notifications\Concerns\FormatsEventMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent the day before a session to the members holding a confirmed seat.
 *
 * This is transactional — it follows from the member's own registration, not
 * from the announcement subscription — so it carries no unsubscribe link. The
 * way to stop receiving it is to give up the seat, which the button does.
 */
class EventReminderNotification extends Notification implements ShouldQueue
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
            ?: ($this->session->focus ?: trans('messages.emails.reminder.fallback_title', [], $locale));

        return (new MailMessage)
            ->subject(trans('messages.emails.reminder.subject', ['title' => $title], $locale))
            ->view(
                ['emails.event-reminder', 'emails.event-reminder-text'],
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
