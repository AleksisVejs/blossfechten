<?php

namespace App\Jobs;

use App\Models\TrainingSession;
use App\Models\User;
use App\Notifications\NewEventNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class AnnounceTrainingSession implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** Release the uniqueness lock even if a worker is killed mid-run. */
    public int $uniqueFor = 600;

    public function __construct(public int $sessionId)
    {
        $this->onQueue('mail');
    }

    public function uniqueId(): string
    {
        return (string) $this->sessionId;
    }

    public function handle(): void
    {
        $session = TrainingSession::find($this->sessionId);

        if ($session === null || ! $session->isAnnounceable()) {
            return;
        }

        // Claim the session atomically: whichever worker wins the conditional
        // update owns the announcement, so a retry or a double dispatch can
        // never mail the club twice about the same event.
        $claimed = TrainingSession::whereKey($session->id)
            ->whereNull('notified_at')
            ->update(['notified_at' => now()]);

        if ($claimed === 0) {
            return;
        }

        $recipients = 0;

        User::query()->eventSubscribers()->orderBy('id')
            ->chunkById(100, function ($users) use ($session, &$recipients) {
                Notification::send($users, new NewEventNotification($session));
                $recipients += $users->count();
            });

        Log::info('Queued new-event announcement', [
            'training_session_id' => $session->id,
            'recipients' => $recipients,
        ]);
    }
}
