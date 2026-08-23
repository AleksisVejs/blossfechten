<?php

namespace Tests\Feature;

use App\Jobs\NotifyPromotedFromWaitlist;
use App\Models\Registration;
use App\Models\TrainingSession;
use App\Models\User;
use App\Notifications\EventPromotedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EventPromotedTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makeSession(array $overrides = []): TrainingSession
    {
        return TrainingSession::create(array_merge([
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addWeek()->addHours(2),
            'location' => 'Ādmiņu iela 4, Rīga',
            'focus' => 'Longsword',
            'title' => ['lv' => 'Vasaras nometne', 'en' => 'Summer camp'],
            'description' => ['lv' => 'Trīs dienu nometne.', 'en' => 'Three day camp.'],
            'capacity' => 1,
        ], $overrides));
    }

    private function register(User $user, TrainingSession $session, string $status, ?string $at = null): Registration
    {
        $registration = Registration::create([
            'user_id' => $user->id,
            'training_session_id' => $session->id,
            'status' => $status,
        ]);

        if ($at !== null) {
            $registration->forceFill(['created_at' => $at])->save();
        }

        return $registration->fresh();
    }

    private function editPayload(TrainingSession $session, array $overrides = []): array
    {
        return array_merge([
            'starts_at' => $session->starts_at->format('Y-m-d\TH:i:s'),
            'ends_at' => $session->ends_at->format('Y-m-d\TH:i:s'),
            'location' => $session->location,
            'capacity' => $session->capacity,
        ], $overrides);
    }

    public function test_the_member_who_moves_up_is_told_they_have_a_seat(): void
    {
        Notification::fake();

        $session = $this->makeSession();
        $holder = User::factory()->create();
        $waiting = User::factory()->create();

        $this->register($holder, $session, 'confirmed');
        $this->register($waiting, $session, 'waitlist');

        $this->actingAs($holder)
            ->deleteJson("/api/trainings/{$session->id}/register")
            ->assertNoContent();

        Notification::assertSentTo($waiting, EventPromotedNotification::class);
    }

    public function test_only_the_member_who_actually_moved_up_is_mailed(): void
    {
        Notification::fake();

        $session = $this->makeSession();
        $holder = User::factory()->create();
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->register($holder, $session, 'confirmed');
        $this->register($first, $session, 'waitlist', '2026-01-01 10:00:00');
        $this->register($second, $session, 'waitlist', '2026-01-01 11:00:00');

        $this->actingAs($holder)->deleteJson("/api/trainings/{$session->id}/register")->assertNoContent();

        Notification::assertSentTo($first, EventPromotedNotification::class);
        // Still waiting, so still nothing to tell them.
        Notification::assertNotSentTo($second, EventPromotedNotification::class);
    }

    public function test_giving_up_a_seat_with_nobody_waiting_mails_nobody(): void
    {
        Notification::fake();

        $session = $this->makeSession(['capacity' => 5]);
        $holder = User::factory()->create();
        $this->register($holder, $session, 'confirmed');

        $this->actingAs($holder)->deleteJson("/api/trainings/{$session->id}/register")->assertNoContent();

        Notification::assertNothingSent();
    }

    public function test_raising_the_capacity_tells_everyone_it_let_in(): void
    {
        Notification::fake();

        $session = $this->makeSession();
        $this->register(User::factory()->create(), $session, 'confirmed');

        $first = User::factory()->create();
        $second = User::factory()->create();
        $third = User::factory()->create();

        $this->register($first, $session, 'waitlist', '2026-01-01 10:00:00');
        $this->register($second, $session, 'waitlist', '2026-01-01 11:00:00');
        $this->register($third, $session, 'waitlist', '2026-01-01 12:00:00');

        $this->actingAs($this->admin())
            ->putJson("/api/admin/trainings/{$session->id}", $this->editPayload($session, ['capacity' => 3]))
            ->assertOk();

        Notification::assertSentTo($first, EventPromotedNotification::class);
        Notification::assertSentTo($second, EventPromotedNotification::class);
        Notification::assertNotSentTo($third, EventPromotedNotification::class);
    }

    public function test_an_ordinary_edit_promotes_nobody_and_mails_nobody(): void
    {
        Notification::fake();

        $session = $this->makeSession();
        $this->register(User::factory()->create(), $session, 'confirmed');
        $waiting = User::factory()->create();
        $this->register($waiting, $session, 'waitlist');

        $this->actingAs($this->admin())
            ->putJson("/api/admin/trainings/{$session->id}", $this->editPayload($session, ['location' => 'Jelgava']))
            ->assertOk();

        Notification::assertNotSentTo($waiting, EventPromotedNotification::class);
    }

    public function test_an_unverified_member_is_promoted_but_not_mailed(): void
    {
        Notification::fake();

        $session = $this->makeSession();
        $holder = User::factory()->create();
        $waiting = User::factory()->unverified()->create();

        $this->register($holder, $session, 'confirmed');
        $promoted = $this->register($waiting, $session, 'waitlist');

        $this->actingAs($holder)->deleteJson("/api/trainings/{$session->id}/register")->assertNoContent();

        // The seat is genuinely theirs — only the mail is withheld, exactly as
        // the reminder and change notices do for unverified addresses.
        $this->assertSame('confirmed', $promoted->fresh()->status);
        Notification::assertNotSentTo($waiting, EventPromotedNotification::class);
    }

    public function test_the_promoted_member_reads_it_in_their_own_language(): void
    {
        Notification::fake();

        $session = $this->makeSession();
        $holder = User::factory()->create();
        $waiting = User::factory()->create(['locale' => 'de']);

        $this->register($holder, $session, 'confirmed');
        $this->register($waiting, $session, 'waitlist');

        $this->actingAs($holder)->deleteJson("/api/trainings/{$session->id}/register")->assertNoContent();

        Notification::assertSentTo($waiting, EventPromotedNotification::class, function ($notification) use ($waiting) {
            $mail = $notification->toMail($waiting);

            return $mail->subject === trans('messages.emails.promoted.subject', ['title' => 'Summer camp'], 'de');
        });
    }

    public function test_the_email_renders_with_the_details_and_no_unsubscribe_link(): void
    {
        $session = $this->makeSession();
        $waiting = User::factory()->create(['name' => 'Jana', 'locale' => 'en']);

        $rendered = (new EventPromotedNotification($session))->toMail($waiting)->render();

        $this->assertStringContainsString('Summer camp', $rendered);
        $this->assertStringContainsString('Ādmiņu iela 4, Rīga', $rendered);
        $this->assertStringContainsString('Jana', $rendered);
        $this->assertStringContainsString(trans('messages.emails.promoted.heading', [], 'en'), $rendered);

        // Transactional: it follows from their own registration, so there is
        // nothing to unsubscribe from.
        $this->assertStringNotContainsString('unsubscribe', strtolower($rendered));
    }

    public function test_a_member_who_opted_out_of_announcements_is_still_told(): void
    {
        Notification::fake();

        $session = $this->makeSession();
        $holder = User::factory()->create();
        $waiting = User::factory()->create(['notify_new_events' => false]);

        $this->register($holder, $session, 'confirmed');
        $this->register($waiting, $session, 'waitlist');

        $this->actingAs($holder)->deleteJson("/api/trainings/{$session->id}/register")->assertNoContent();

        Notification::assertSentTo($waiting, EventPromotedNotification::class);
    }

    public function test_a_session_cancelled_before_the_job_runs_drops_the_notice(): void
    {
        Notification::fake();

        $session = $this->makeSession();
        $waiting = User::factory()->create();
        $promoted = $this->register($waiting, $session, 'confirmed');

        // The queue can lag by a cron interval on shared hosting, so the world
        // is allowed to move between the promotion and the mail going out.
        $session->forceFill(['cancelled' => true])->save();

        (new NotifyPromotedFromWaitlist($session->id, [$promoted->id]))->handle();

        Notification::assertNotSentTo($waiting, EventPromotedNotification::class);
    }

    public function test_a_seat_given_straight_back_drops_the_notice(): void
    {
        Notification::fake();

        $session = $this->makeSession();
        $waiting = User::factory()->create();
        $promoted = $this->register($waiting, $session, 'confirmed');

        $promoted->delete();

        (new NotifyPromotedFromWaitlist($session->id, [$promoted->id]))->handle();

        Notification::assertNothingSent();
    }

    public function test_a_cancelled_session_never_tells_anyone_they_have_a_seat(): void
    {
        Notification::fake();

        $session = $this->makeSession(['capacity' => 5]);
        $waiting = User::factory()->create();
        $this->register($waiting, $session, 'waitlist');

        $this->actingAs($this->admin())
            ->putJson("/api/admin/trainings/{$session->id}", $this->editPayload($session, ['cancelled' => true]))
            ->assertOk();

        Notification::assertNotSentTo($waiting, EventPromotedNotification::class);
    }
}
