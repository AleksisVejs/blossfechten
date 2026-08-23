<?php

namespace App\Console\Commands;

use App\Jobs\RemindTrainingSession;
use App\Models\TrainingSession;
use Illuminate\Console\Command;

class SendDueReminders extends Command
{
    protected $signature = 'events:send-reminders {--pretend : List what would be reminded without queueing anything}';

    protected $description = 'Queue day-before reminders for sessions entering the next 24 hours';

    public function handle(): int
    {
        $due = TrainingSession::query()->dueForReminder()->orderBy('starts_at')->get();

        if ($due->isEmpty()) {
            $this->line('No sessions due for a reminder.');

            return self::SUCCESS;
        }

        foreach ($due as $session) {
            $when = $session->starts_at?->format('d.m.Y H:i');

            if ($this->option('pretend')) {
                $this->line("Would remind for session {$session->id} starting {$when}.");
                continue;
            }

            RemindTrainingSession::dispatch($session->id);
            $this->line("Queued reminder for session {$session->id} starting {$when}.");
        }

        return self::SUCCESS;
    }
}
