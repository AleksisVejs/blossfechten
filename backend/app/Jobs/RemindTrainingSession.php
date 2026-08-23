<?php

namespace App\Jobs;

use App\Models\TrainingSession;
use App\Models\User;
use App\Notifications\EventReminderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class RemindTrainingSession implements ShouldQueue, ShouldBeUnique
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

        if ($session === null) {
            return;
        }

        // Re-check under the job rather than trusting the dispatching command:
        // the session may have been cancelled or moved since it was queued.
        if (! TrainingSession::whereKey($session->id)->dueForReminder()->exists()) {
            return;
        }

        // Claim it atomically, exactly as the announcement does, so a retry or
        // a double dispatch cannot remind the same people twice.
        $claimed = TrainingSession::whereKey($session->id)
            ->whereNull('reminded_at')
            ->update(['reminded_at' => now()]);

        if ($claimed === 0) {
            return;
        }

        $recipients = 0;

        // Only confirmed seats: a waitlisted member has nothing to turn up for.
        User::query()
            ->whereNotNull('email_verified_at')
            ->whereHas('registrations', fn($query) => $query
                ->where('training_session_id', $session->id)
                ->where('status', 'confirmed'))
            ->orderBy('id')
            ->chunkById(100, function ($users) use ($session, &$recipients) {
                Notification::send($users, new EventReminderNotification($session));
                $recipients += $users->count();
            });

        Log::info('Queued event reminder', [
            'training_session_id' => $session->id,
            'recipients' => $recipients,
        ]);
    }
}
