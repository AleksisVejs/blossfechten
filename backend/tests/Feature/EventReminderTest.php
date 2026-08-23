<?php

namespace Tests\Feature;

use App\Jobs\RemindTrainingSession;
use App\Models\Registration;
use App\Models\TrainingSession;
use App\Models\User;
use App\Notifications\EventReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EventReminderTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /** A session inside the reminder window unless overridden. */
    private function makeSession(array $overrides = []): TrainingSession
    {
        return TrainingSession::create(array_merge([
            'starts_at' => now()->addHours(12),
            'ends_at' => now()->addHours(14),
            'location' => 'Ādmiņu iela 4, Rīga',
            'focus' => 'Longsword',
            'title' => ['lv' => 'Vasaras nometne', 'en' => 'Summer camp'],
            'description' => ['lv' => 'Trīs dienu nometne.', 'en' => 'Three day camp.'],
            'capacity' => 20,
            'send_reminder' => true,
        ], $overrides));
    }

    private function register(User $user, TrainingSession $session, string $status = 'confirmed'): Registration
    {
        return Registration::create([
            'user_id' => $user->id,
            'training_session_id' => $session->id,
            'status' => $status,
        ]);
    }

    public function test_reminder_reaches_confirmed_registrants_only(): void
    {
        Notification::fake();

        $session = $this->makeSession();

        $confirmed = User::factory()->create();
        $waitlisted = User::factory()->create();
        $cancelledSeat = User::factory()->create();
        $unregistered = User::factory()->create();
        $unverified = User::factory()->unverified()->create();

        $this->register($confirmed, $session);
        $this->register($waitlisted, $session, 'waitlist');
        $this->register($cancelledSeat, $session, 'cancelled');
        $this->register($unverified, $session);

        $this->artisan('events:send-reminders')->assertSuccessful();

        Notification::assertSentTo($confirmed, EventReminderNotification::class);
        Notification::assertNotSentTo($waitlisted, EventReminderNotification::class);
        Notification::assertNotSentTo($cancelledSeat, EventReminderNotification::class);
        Notification::assertNotSentTo($unregistered, EventReminderNotification::class);
        Notification::assertNotSentTo($unverified, EventReminderNotification::class);
    }

    public function test_reminder_still_reaches_members_who_opted_out_of_announcements(): void
    {
        Notification::fake();

        // Announcement opt-out is about the club newsletter. A reminder follows
        // from the member's own registration, so it must still arrive.
        $session = $this->makeSession();
        $member = User::factory()->create(['notify_new_events' => false]);
        $this->register($member, $session);

        $this->artisan('events:send-reminders')->assertSuccessful();

        Notification::assertSentTo($member, EventReminderNotification::class);
    }

    public function test_no_reminder_when_the_admin_switched_it_off(): void
    {
        Notification::fake();

        $session = $this->makeSession(['send_reminder' => false]);
        $member = User::factory()->create();
        $this->register($member, $session);

        $this->artisan('events:send-reminders')->assertSuccessful();

        Notification::assertNothingSent();
        $this->assertNull($session->fresh()->reminded_at);
    }

    public function test_no_reminder_for_cancelled_sessions(): void
    {
        Notification::fake();

        $session = $this->makeSession(['cancelled' => true]);
        $member = User::factory()->create();
        $this->register($member, $session);

        $this->artisan('events:send-reminders')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_no_reminder_for_sessions_further_out_than_a_day(): void
    {
        Notification::fake();

        $session = $this->makeSession([
            'starts_at' => now()->addDays(3),
            'ends_at' => now()->addDays(3)->addHours(2),
        ]);
        $member = User::factory()->create();
        $this->register($member, $session);

        $this->artisan('events:send-reminders')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_no_reminder_for_sessions_that_already_started(): void
    {
        Notification::fake();

        $session = $this->makeSession([
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
        ]);
        $member = User::factory()->create();
        $this->register($member, $session);

        $this->artisan('events:send-reminders')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_reminder_is_sent_only_once_however_often_the_command_runs(): void
    {
        Notification::fake();

        $session = $this->makeSession();
        $member = User::factory()->create();
        $this->register($member, $session);

        $this->artisan('events:send-reminders')->assertSuccessful();
        $this->artisan('events:send-reminders')->assertSuccessful();
        $this->artisan('events:send-reminders')->assertSuccessful();

        Notification::assertSentToTimes($member, EventReminderNotification::class, 1);
        $this->assertNotNull($session->fresh()->reminded_at);
    }

    public function test_a_just_announced_session_does_not_get_a_reminder_minutes_later(): void
    {
        Notification::fake();

        // Event created less than a day before it starts, announced just now:
        // the reminder would otherwise chase the announcement out the door.
        $session = $this->makeSession();
        $session->markAnnounced();

        $member = User::factory()->create();
        $this->register($member, $session);

        $this->artisan('events:send-reminders')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_a_session_announced_hours_ago_still_gets_its_reminder(): void
    {
        Notification::fake();

        $session = $this->makeSession();
        $session->forceFill(['notified_at' => now()->subHours(5)])->save();

        $member = User::factory()->create();
        $this->register($member, $session);

        $this->artisan('events:send-reminders')->assertSuccessful();

        Notification::assertSentTo($member, EventReminderNotification::class);
    }

    public function test_pretend_mode_reports_without_queueing_anything(): void
    {
        Notification::fake();

        $session = $this->makeSession();
        $member = User::factory()->create();
        $this->register($member, $session);

        $this->artisan('events:send-reminders --pretend')->assertSuccessful();

        Notification::assertNothingSent();
        $this->assertNull($session->fresh()->reminded_at);
    }

    public function test_moving_a_reminded_session_further_out_re_arms_the_reminder(): void
    {
        Notification::fake();

        $session = $this->makeSession();
        $member = User::factory()->create();
        $this->register($member, $session);

        $this->artisan('events:send-reminders')->assertSuccessful();
        $this->assertNotNull($session->fresh()->reminded_at);

        // Admin pushes it back a fortnight — members reminded about the old
        // date would otherwise hear nothing before the new one.
        $this->actingAs($this->admin())
            ->putJson("/api/admin/trainings/{$session->id}", [
                'starts_at' => now()->addDays(14)->format('Y-m-d\TH:i:s'),
                'ends_at' => now()->addDays(14)->addHours(2)->format('Y-m-d\TH:i:s'),
                'location' => $session->location,
                'focus' => $session->focus,
                'title' => $session->title,
                'description' => $session->description,
                'capacity' => $session->capacity,
            ])
            ->assertOk();

        $this->assertNull($session->fresh()->reminded_at);
    }

    public function test_a_small_edit_does_not_re_arm_an_already_sent_reminder(): void
    {
        Notification::fake();

        $session = $this->makeSession();
        $member = User::factory()->create();
        $this->register($member, $session);

        $this->artisan('events:send-reminders')->assertSuccessful();
        $remindedAt = $session->fresh()->reminded_at;

        $this->actingAs($this->admin())
            ->putJson("/api/admin/trainings/{$session->id}", [
                'starts_at' => $session->starts_at->format('Y-m-d\TH:i:s'),
                'ends_at' => $session->ends_at->format('Y-m-d\TH:i:s'),
                'location' => 'Jauna vieta',
                'focus' => $session->focus,
                'title' => $session->title,
                'description' => $session->description,
                'capacity' => $session->capacity,
            ])
            ->assertOk();

        $this->assertEquals($remindedAt, $session->fresh()->reminded_at);
    }

    public function test_a_deleted_session_quietly_drops_its_pending_reminder(): void
    {
        Notification::fake();

        $session = $this->makeSession();
        $member = User::factory()->create();
        $this->register($member, $session);

        $id = $session->id;
        $session->delete();

        RemindTrainingSession::dispatch($id);

        Notification::assertNothingSent();
    }

    public function test_reminder_renders_in_the_members_own_language(): void
    {
        $session = $this->makeSession();
        $member = User::factory()->create(['locale' => 'de', 'name' => 'Klaus']);
        $this->register($member, $session);

        $this->artisan('events:send-reminders')->assertSuccessful();

        $message = app('mailer')->getSymfonyTransport()->messages()->first()->getOriginalMessage();
        $body = $message->getHtmlBody();

        $this->assertStringContainsString('Erinnerung', $message->getSubject());
        $this->assertStringContainsString('Erinnerung an dein nächstes Training', $body);
        $this->assertStringContainsString('Hallo, Klaus!', $body);
        $this->assertStringContainsString('Ādmiņu iela 4, Rīga', $body);

        // Transactional: no unsubscribe link, and no bulk headers either.
        $this->assertStringNotContainsString('/unsubscribe', $body);
        $this->assertNull($message->getHeaders()->get('List-Unsubscribe'));
    }

    public function test_reminder_falls_back_when_the_members_language_is_blank(): void
    {
        $session = $this->makeSession(['title' => ['lv' => 'Tikai latviski']]);
        $member = User::factory()->create(['locale' => 'ru']);
        $this->register($member, $session);

        $this->artisan('events:send-reminders')->assertSuccessful();

        $message = app('mailer')->getSymfonyTransport()->messages()->first()->getOriginalMessage();

        // Russian chrome, Latvian title — better than mailing a blank line.
        $this->assertStringContainsString('Напоминание', $message->getSubject());
        $this->assertStringContainsString('Tikai latviski', $message->getHtmlBody());
    }
}
