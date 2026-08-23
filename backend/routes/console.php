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

    // Whoever ran this command vouched for the address, and there is no
    // verification mail on this path to click. Leaving it unverified would
    // quietly exclude the account from every notification fan-out.
    $user->forceFill(['email_verified_at' => now()])->save();

    $this->newLine();
    $this->info("User created successfully with ID {$user->id}.");
    return 0;
})->purpose('Interactively create an application user');

Artisan::command('user:list {--unverified : Only accounts that never confirmed their address}', function () {
    $users = User::query()
        ->when($this->option('unverified'), fn($query) => $query->whereNull('email_verified_at'))
        ->orderByDesc('role')
        ->orderBy('name')
        ->get();

    if ($users->isEmpty()) {
        $this->info($this->option('unverified') ? 'Every account is verified.' : 'No accounts yet.');
        return 0;
    }

    $this->table(
        ['ID', 'Name', 'Email', 'Role', 'Locale', 'Email confirmed'],
        $users->map(fn(User $user) => [
            $user->id,
            $user->name,
            $user->email,
            $user->role,
            $user->locale,
            // The column that decides whether this account hears from us at all.
            $user->email_verified_at?->format('Y-m-d') ?? 'NO — receives no event mail',
        ])->all()
    );

    $unverified = $users->whereNull('email_verified_at');
    if ($unverified->isNotEmpty()) {
        $this->newLine();
        $this->warn($unverified->count() . ' account(s) never confirmed an address. Fix one with:');
        $this->line('  php artisan user:verify ' . $unverified->first()->email);
    }

    return 0;
})->purpose('List accounts and show which ones never confirmed their email');

Artisan::command('user:verify {email}', function (string $email) {
    $user = User::where('email', strtolower(trim($email)))->first();

    if (! $user) {
        $this->error("No user with the address {$email}.");
        return 1;
    }

    if ($user->hasVerifiedEmail()) {
        $this->info("{$user->email} is already verified.");
        return 0;
    }

    $user->markEmailAsVerified();
    $this->info("Marked {$user->email} as verified.");
    return 0;
})->purpose('Mark an account email as verified without sending a verification mail');

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
