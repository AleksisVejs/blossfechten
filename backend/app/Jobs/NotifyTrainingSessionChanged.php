<?php

namespace App\Jobs;

use App\Models\TrainingSession;
use App\Models\User;
use App\Notifications\EventChangedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotifyTrainingSessionChanged implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @param array<int, array<string, mixed>> $changes diff built by the controller
     */
    public function __construct(public int $sessionId, public array $changes)
    {
        $this->onQueue('mail');
    }

    public function handle(): void
    {
        $session = TrainingSession::find($this->sessionId);

        if ($session === null || $this->changes === []) {
            return;
        }

        $recipients = 0;

        // Waitlisted members are included: a moved or cancelled session matters
        // just as much to someone holding a place in the queue for a seat.
        User::query()
            ->whereNotNull('email_verified_at')
            ->whereHas('registrations', fn($query) => $query
                ->where('training_session_id', $session->id)
                ->whereIn('status', ['confirmed', 'waitlist']))
            ->orderBy('id')
            ->chunkById(100, function ($users) use ($session, &$recipients) {
                Notification::send($users, new EventChangedNotification($session, $this->changes));
                $recipients += $users->count();
            });

        Log::info('Queued event change notice', [
            'training_session_id' => $session->id,
            'recipients' => $recipients,
            'fields' => array_column($this->changes, 'field'),
        ]);
    }
}
