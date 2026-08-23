<?php

namespace App\Notifications;

use App\Models\TrainingSession;
use App\Notifications\Concerns\FormatsEventMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to everyone holding a seat or a waitlist place when the admin edits a
 * session and asks for the change to go out. Also transactional: it follows
 * from the member's registration, so there is no unsubscribe link.
 *
 * $changes is the diff built by the controller, e.g.
 *   [['field' => 'when', 'from' => '20.09.2026 11:00-14:00', 'to' => '...']]
 * Localizable fields carry their raw json arrays so each member reads the
 * change in their own language.
 */
class EventChangedNotification extends Notification implements ShouldQueue
{
    use FormatsEventMail, Queueable;

    public bool $deleteWhenMissingModels = true;

    public function __construct(public TrainingSession $session, public array $changes)
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
        $cancelled = $this->isCancellation();

        $title = $this->localized($this->session->title, $locale)
            ?: ($this->session->focus ?: trans('messages.emails.changed.fallback_title', [], $locale));

        $subjectKey = $cancelled
            ? 'messages.emails.changed.subject_cancelled'
            : 'messages.emails.changed.subject';

        return (new MailMessage)
            ->subject(trans($subjectKey, ['title' => $title], $locale))
            ->view(
                ['emails.event-changed', 'emails.event-changed-text'],
                [
                    'recipientName' => $notifiable->name ?? '',
                    'locale' => $locale,
                    'eventTitle' => $title,
                    'isCancelled' => $cancelled,
                    'isReinstated' => $this->hasField('reinstated'),
                    'changes' => $this->renderChanges($locale),
                    'eventWhen' => $this->formatWhen($this->session->starts_at, $this->session->ends_at),
                    'eventLocation' => $this->session->location,
                    'eventUrl' => $this->frontendUrl() . '/schedule',
                ]
            );
    }

    private function isCancellation(): bool
    {
        return $this->hasField('cancelled');
    }

    private function hasField(string $field): bool
    {
        return in_array($field, array_column($this->changes, 'field'), true);
    }

    /**
     * Turn the raw diff into printable rows. Cancelling and reinstating are
     * rendered as their own banner rather than as a row with nothing in it,
     * so both are skipped here.
     *
     * @return array<int, array{label: string, from: ?string, to: ?string}>
     */
    private function renderChanges(string $locale): array
    {
        $rows = [];

        foreach ($this->changes as $change) {
            $field = $change['field'] ?? '';

            if ($field === '' || $field === 'cancelled' || $field === 'reinstated') {
                continue;
            }

            $from = $change['from'] ?? null;
            $to = $change['to'] ?? null;

            // Title and description arrive as per-locale arrays.
            if (is_array($from)) {
                $from = $this->localized($from, $locale);
            }
            if (is_array($to)) {
                $to = $this->localized($to, $locale);
            }

            $rows[] = [
                'label' => trans("messages.emails.changed.fields.{$field}", [], $locale),
                'from' => $from !== null ? (string) $from : null,
                'to' => $to !== null ? (string) $to : null,
            ];
        }

        return $rows;
    }
}
