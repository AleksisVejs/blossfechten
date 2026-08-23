<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Validator;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('user:create', function () {
    $this->info('Create a new user');
    $this->newLine();

    $name = trim((string) $this->ask('Full name'));
    $email = strtolower(trim((string) $this->ask('Email address')));
    $password = (string) $this->secret('Password (min 8 chars)');
    $passwordConfirmation = (string) $this->secret('Confirm password');

    $roleChoice = $this->choice(
        'Select role',
        ['User', 'Admin'],
        default: 0
    );
    $role = strtolower($roleChoice);

    $phone = trim((string) $this->ask('Phone (optional)', ''));
    $rank = trim((string) $this->ask('Rank (optional)', ''));
    $locale = trim((string) $this->choice(
        'Preferred locale',
        ['en', 'de', 'lv', 'ru', 'cs'],
        default: 0
    ));

    $validator = Validator::make([
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'password_confirmation' => $passwordConfirmation,
        'role' => $role,
        'locale' => $locale,
    ], [
        'name' => ['required', 'string', 'min:2', 'max:255'],
        'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users,email'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
        'role' => ['required', 'in:user,admin'],
        'locale' => ['required', 'in:en,de,lv,ru,cs'],
    ]);

    if ($validator->fails()) {
        $this->newLine();
        $this->error('Could not create user:');
        foreach ($validator->errors()->all() as $error) {
            $this->line("- {$error}");
        }
        return 1;
    }

    $this->newLine();
    $this->table(['Field', 'Value'], [
        ['Name', $name],
        ['Email', $email],
        ['Role', $role],
        ['Locale', $locale],
        ['Phone', $phone !== '' ? $phone : '(empty)'],
        ['Rank', $rank !== '' ? $rank : '(empty)'],
    ]);

    if (! $this->confirm('Create this user now?', true)) {
        $this->warn('User creation cancelled.');
        return 2;
    }

    $user = User::create([
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'role' => $role,
        'phone' => $phone !== '' ? $phone : null,
        'rank' => $rank !== '' ? $rank : null,
        'locale' => $locale,
    ]);

    $this->newLine();
    $this->info("User created successfully with ID {$user->id}.");
    return 0;
})->purpose('Interactively create an application user');

/*
 * Shared cPanel hosting has no long-running worker process, so the scheduler
 * drains the queue instead. The cron on this account fires `schedule:run` every
 * ten minutes, so announcement mail goes out within that window.
 *
 * everyMinute() means "whenever schedule:run fires" — leave it alone if the cron
 * interval changes. --stop-when-empty exits the moment the queue is drained;
 * --max-time is only a ceiling, set well inside the gap between cron runs. The
 * overlap lock expires after 10 minutes so a killed worker cannot wedge it.
 */
Schedule::command('queue:work --queue=mail,default --stop-when-empty --max-time=300 --tries=3')
    ->everyMinute()
    ->withoutOverlapping(10);

/*
 * Day-before reminders. Checked every fifteen minutes rather than every minute
 * — the reminder only needs to be roughly a day ahead, and this keeps the
 * per-minute cron cheap. A session entering its last 24 hours is therefore
 * reminded within a quarter of an hour of crossing that line.
 */
Schedule::command('events:send-reminders')
    ->everyFifteenMinutes()
    ->withoutOverlapping(10);
