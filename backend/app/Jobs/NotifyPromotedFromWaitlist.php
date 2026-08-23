<?php

namespace App\Jobs;

use App\Models\TrainingSession;
use App\Models\User;
use App\Notifications\EventPromotedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Tells the members who were just moved off the waiting list that they now
 * hold a seat.
 *
 * Dispatched with registration ids rather than user ids: the same member may
 * be on several waiting lists, and only the row that actually moved is worth
 * mailing about.
 */
class NotifyPromotedFromWaitlist implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @param array<int, int> $registrationIds rows promoted to 'confirmed' */
    public function __construct(public int $sessionId, public array $registrationIds)
    {
        $this->onQueue('mail');
    }

    public function handle(): void
    {
        $session = TrainingSession::find($this->sessionId);

        if ($session === null || $this->registrationIds === []) {
            return;
        }

        // Re-check under the job rather than trusting the dispatching
        // controller: between the promotion and this running, the session may
        // have been cancelled, or the member may have given the seat straight
        // back. Mailing "you have a seat" after either would be worse than
        // saying nothing.
        if ($session->cancelled) {
            return;
        }

        $users = User::query()
            ->whereNotNull('email_verified_at')
            ->whereHas('registrations', fn($query) => $query
                ->whereIn('id', $this->registrationIds)
                ->where('training_session_id', $session->id)
                ->where('status', 'confirmed'))
            ->orderBy('id')
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new EventPromotedNotification($session));

        Log::info('Queued waitlist promotion notice', [
            'training_session_id' => $session->id,
            'recipients' => $users->count(),
        ]);
    }
}
