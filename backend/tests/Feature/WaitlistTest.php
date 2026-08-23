<?php

namespace Tests\Feature;

use App\Models\Registration;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WaitlistTest extends TestCase
{
    use RefreshDatabase;

    private function trainingSession(int $capacity = 2, array $overrides = []): TrainingSession
    {
        return TrainingSession::create(array_merge([
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addWeek()->addHours(2),
            'location' => 'Ādmiņu iela 4, Rīga',
            'capacity' => $capacity,
        ], $overrides));
    }

    private function seat(TrainingSession $session, User $user, string $status, ?string $at = null): Registration
    {
        $registration = Registration::create([
            'user_id' => $user->id,
            'training_session_id' => $session->id,
            'status' => $status,
        ]);

        if ($at !== null) {
            // Waitlist order is by arrival, so the tests need to control it.
            $registration->forceFill(['created_at' => $at])->save();
        }

        return $registration->fresh();
    }

    public function test_a_full_session_puts_the_next_member_on_the_waitlist(): void
    {
        $session = $this->trainingSession(capacity: 1);
        $this->seat($session, User::factory()->create(), 'confirmed');

        $latecomer = User::factory()->create();

        $this->actingAs($latecomer)
            ->postJson("/api/trainings/{$session->id}/register")
            ->assertCreated()
            ->assertJsonPath('data.status', 'waitlist')
            ->assertJsonPath('message', __('messages.training.waitlisted'));
    }

    public function test_giving_up_a_seat_promotes_whoever_waited_longest(): void
    {
        $session = $this->trainingSession(capacity: 1);

        $holder = User::factory()->create();
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->seat($session, $holder, 'confirmed');
        $firstInLine = $this->seat($session, $first, 'waitlist', '2026-01-01 10:00:00');
        $secondInLine = $this->seat($session, $second, 'waitlist', '2026-01-01 11:00:00');

        $this->actingAs($holder)
            ->deleteJson("/api/trainings/{$session->id}/register")
            ->assertNoContent();

        $this->assertSame('confirmed', $firstInLine->fresh()->status);
        $this->assertSame('waitlist', $secondInLine->fresh()->status, 'Only the freed seat should be filled.');
    }

    public function test_promotion_never_overfills_the_session(): void
    {
        $session = $this->trainingSession(capacity: 2);

        $holder = User::factory()->create();
        $this->seat($session, $holder, 'confirmed');
        $this->seat($session, User::factory()->create(), 'confirmed');

        $waiting = collect(range(1, 3))->map(
            fn(int $i) => $this->seat($session, User::factory()->create(), 'waitlist', "2026-01-0{$i} 10:00:00")
        );

        $this->actingAs($holder)->deleteJson("/api/trainings/{$session->id}/register")->assertNoContent();

        $this->assertSame(2, $session->fresh()->confirmedCount());
        $this->assertSame('confirmed', $waiting[0]->fresh()->status);
        $this->assertSame('waitlist', $waiting[1]->fresh()->status);
        $this->assertSame('waitlist', $waiting[2]->fresh()->status);
    }

    public function test_raising_the_capacity_drains_the_waiting_list(): void
    {
        $session = $this->trainingSession(capacity: 1);
        $this->seat($session, User::factory()->create(), 'confirmed');

        $first = $this->seat($session, User::factory()->create(), 'waitlist', '2026-01-01 10:00:00');
        $second = $this->seat($session, User::factory()->create(), 'waitlist', '2026-01-01 11:00:00');
        $third = $this->seat($session, User::factory()->create(), 'waitlist', '2026-01-01 12:00:00');

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->putJson("/api/admin/trainings/{$session->id}", [
                'starts_at' => $session->starts_at->format('Y-m-d\TH:i:s'),
                'ends_at' => $session->ends_at->format('Y-m-d\TH:i:s'),
                'location' => $session->location,
                'capacity' => 3,
            ])->assertOk();

        $this->assertSame('confirmed', $first->fresh()->status);
        $this->assertSame('confirmed', $second->fresh()->status);
        $this->assertSame('waitlist', $third->fresh()->status);
    }

    public function test_a_cancelled_session_does_not_promote_anyone(): void
    {
        $session = $this->trainingSession(capacity: 5);
        $waiting = $this->seat($session, User::factory()->create(), 'waitlist');

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->putJson("/api/admin/trainings/{$session->id}", [
                'starts_at' => $session->starts_at->format('Y-m-d\TH:i:s'),
                'ends_at' => $session->ends_at->format('Y-m-d\TH:i:s'),
                'location' => $session->location,
                'capacity' => 5,
                'cancelled' => true,
            ])->assertOk();

        $this->assertSame('waitlist', $waiting->fresh()->status);
    }

    public function test_re_registering_does_not_cost_a_member_the_seat_they_hold(): void
    {
        $session = $this->trainingSession(capacity: 1);
        $holder = User::factory()->create();
        $this->seat($session, $holder, 'confirmed');

        // The session is now full — including this member's own seat.
        $this->actingAs($holder)
            ->postJson("/api/trainings/{$session->id}/register", ['note' => 'bringing a spare feder'])
            ->assertCreated()
            ->assertJsonPath('data.status', 'confirmed');

        $this->assertSame(1, $session->fresh()->confirmedCount());
    }

    public function test_the_schedule_tells_a_member_whether_they_hold_a_seat(): void
    {
        $session = $this->trainingSession(capacity: 1);
        $this->seat($session, User::factory()->create(), 'confirmed');

        $waiting = User::factory()->create();
        $this->seat($session, $waiting, 'waitlist');

        $this->actingAs($waiting)
            ->getJson('/api/trainings')
            ->assertOk()
            ->assertJsonPath('data.0.is_registered', true)
            ->assertJsonPath('data.0.registration_status', 'waitlist');
    }

    public function test_the_public_session_endpoint_does_not_leak_who_is_attending(): void
    {
        $session = $this->trainingSession(capacity: 5);
        $this->seat($session, User::factory()->create(['name' => 'Jane Fencer']), 'confirmed');

        $response = $this->getJson("/api/trainings/{$session->id}")->assertOk();

        $body = $response->getContent();
        $this->assertStringNotContainsString('Jane Fencer', $body);
        $this->assertArrayNotHasKey('registrations', $response->json('data'));

        // The seat count itself is public — that is what "3 seats left" needs.
        $this->assertSame(1, $response->json('data.confirmed_count'));
    }
}
