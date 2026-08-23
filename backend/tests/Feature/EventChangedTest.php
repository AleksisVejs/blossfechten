<?php

namespace Tests\Feature;

use App\Jobs\NotifyTrainingSessionChanged;
use App\Models\Registration;
use App\Models\TrainingSession;
use App\Models\User;
use App\Notifications\EventChangedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EventChangedTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makeSession(array $overrides = []): TrainingSession
    {
        return TrainingSession::create(array_merge([
            'starts_at' => now()->addDays(10)->setTime(11, 0),
            'ends_at' => now()->addDays(10)->setTime(14, 0),
            'location' => 'Ādmiņu iela 4, Rīga',
            'focus' => 'Longsword',
            'title' => ['lv' => 'Vasaras nometne', 'en' => 'Summer camp', 'de' => 'Sommerlager'],
            'description' => ['lv' => 'Trīs dienu nometne.', 'en' => 'Three day camp.'],
            'capacity' => 20,
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

    /** The update endpoint wants the whole record, so start from what is stored. */
    private function payloadFor(TrainingSession $session, array $overrides = []): array
    {
        return array_merge([
            'starts_at' => $session->starts_at->format('Y-m-d\TH:i:s'),
            'ends_at' => $session->ends_at->format('Y-m-d\TH:i:s'),
            'location' => $session->location,
            'focus' => $session->focus,
            'title' => $session->title,
            'description' => $session->description,
            'capacity' => $session->capacity,
            'cancelled' => (bool) $session->cancelled,
            'notify_changes' => true,
        ], $overrides);
    }

    private function update(TrainingSession $session, array $overrides = []): void
    {
        $this->actingAs($this->admin())
            ->putJson("/api/admin/trainings/{$session->id}", $this->payloadFor($session, $overrides))
            ->assertOk();
    }

    private function lastMail(): \Symfony\Component\Mime\Email
    {
        return app('mailer')->getSymfonyTransport()->messages()->first()->getOriginalMessage();
    }

    public function test_change_notice_reaches_everyone_holding_a_seat_or_a_waitlist_place(): void
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

        $this->update($session, ['location' => 'Cita zāle']);

        Notification::assertSentTo($confirmed, EventChangedNotification::class);
        Notification::assertSentTo($waitlisted, EventChangedNotification::class);
        Notification::assertNotSentTo($cancelledSeat, EventChangedNotification::class);
        Notification::assertNotSentTo($unregistered, EventChangedNotification::class);
        Notification::assertNotSentTo($unverified, EventChangedNotification::class);
    }

    public function test_nothing_is_sent_unless_the_admin_asks_for_it(): void
    {
        Notification::fake();

        $session = $this->makeSession();
        $member = User::factory()->create();
        $this->register($member, $session);

        $this->actingAs($this->admin())
            ->putJson("/api/admin/trainings/{$session->id}", $this->payloadFor($session, [
                'location' => 'Cita zāle',
                'notify_changes' => false,
            ]))
            ->assertOk();

        Notification::assertNothingSent();
    }

    public function test_nothing_is_sent_when_no_notable_field_moved(): void
    {
        Notification::fake();

        $session = $this->makeSession();
        $member = User::factory()->create();
        $this->register($member, $session);

        // Capacity is deliberately not a notable field: nobody needs an email
        // because a seat count changed.
        $this->update($session, ['capacity' => 40]);

        Notification::assertNothingSent();
        $this->assertSame(40, $session->fresh()->capacity);
    }

    public function test_saving_with_no_edits_at_all_sends_nothing(): void
    {
        Notification::fake();

        $session = $this->makeSession();
        $member = User::factory()->create();
        $this->register($member, $session);

        $this->update($session);

        Notification::assertNothingSent();
    }

    public function test_a_moved_session_shows_the_old_and_the_new_time(): void
    {
        $session = $this->makeSession();
        $member = User::factory()->create(['locale' => 'lv']);
        $this->register($member, $session);

        $oldWhen = $session->starts_at->format('d.m.Y H:i') . '-' . $session->ends_at->format('H:i');
        $newStart = now()->addDays(11)->setTime(9, 30);
        $newEnd = now()->addDays(11)->setTime(12, 0);

        $this->update($session, [
            'starts_at' => $newStart->format('Y-m-d\TH:i:s'),
            'ends_at' => $newEnd->format('Y-m-d\TH:i:s'),
        ]);

        $body = $this->lastMail()->getHtmlBody();

        $this->assertStringContainsString('Laiks', $body);
        $this->assertStringContainsString($oldWhen, $body);
        $this->assertStringContainsString(
            $newStart->format('d.m.Y H:i') . '-' . $newEnd->format('H:i'),
            $body
        );
    }

    public function test_a_moved_venue_shows_the_old_and_the_new_place(): void
    {
        $session = $this->makeSession();
        $member = User::factory()->create(['locale' => 'lv']);
        $this->register($member, $session);

        $this->update($session, ['location' => 'Sporta halle, Jelgava']);

        $body = $this->lastMail()->getHtmlBody();

        $this->assertStringContainsString('Vieta', $body);
        $this->assertStringContainsString('Ādmiņu iela 4, Rīga', $body);
        $this->assertStringContainsString('Sporta halle, Jelgava', $body);
    }

    public function test_cancelling_reads_as_a_cancellation_rather_than_an_edit(): void
    {
        $session = $this->makeSession();
        $member = User::factory()->create(['locale' => 'lv']);
        $this->register($member, $session);

        $this->update($session, ['cancelled' => true]);

        $message = $this->lastMail();
        $body = $message->getHtmlBody();

        $this->assertStringContainsString('Atcelts', $message->getSubject());
        $this->assertStringContainsString('PASĀKUMS ATCELTS', $body);
        $this->assertStringContainsString('ir atcelts', $body);

        // Nothing to sign up for any more, so no call to action.
        $this->assertStringNotContainsString('Skatīt kalendāru', $body);
    }

    public function test_reinstating_a_cancelled_session_says_it_is_back_on(): void
    {
        $session = $this->makeSession(['cancelled' => true]);
        $member = User::factory()->create(['locale' => 'lv']);
        $this->register($member, $session);

        $this->update($session, ['cancelled' => false]);

        $message = $this->lastMail();
        $body = $message->getHtmlBody();

        $this->assertStringContainsString('PASĀKUMS ATKAL NOTIKS', $body);
        $this->assertStringNotContainsString('PASĀKUMS ATCELTS', $body);
        $this->assertStringContainsString('Izmaiņas', $message->getSubject());
    }

    public function test_one_edit_can_carry_several_changes(): void
    {
        $session = $this->makeSession();
        $member = User::factory()->create(['locale' => 'lv']);
        $this->register($member, $session);

        $this->update($session, [
            'starts_at' => now()->addDays(12)->setTime(10, 0)->format('Y-m-d\TH:i:s'),
            'ends_at' => now()->addDays(12)->setTime(13, 0)->format('Y-m-d\TH:i:s'),
            'location' => 'Cita zāle',
            'title' => ['lv' => 'Ziemas nometne', 'en' => 'Winter camp'],
        ]);

        $body = $this->lastMail()->getHtmlBody();

        $this->assertStringContainsString('Laiks', $body);
        $this->assertStringContainsString('Vieta', $body);
        $this->assertStringContainsString('Nosaukums', $body);
        $this->assertStringContainsString('Vasaras nometne', $body);
        $this->assertStringContainsString('Ziemas nometne', $body);
    }

    public function test_a_changed_description_is_shown_as_it_now_reads(): void
    {
        $session = $this->makeSession();
        $member = User::factory()->create(['locale' => 'lv']);
        $this->register($member, $session);

        $this->update($session, [
            'description' => ['lv' => 'Ņemiet līdzi masku.', 'en' => 'Bring a mask.'],
        ]);

        $body = $this->lastMail()->getHtmlBody();

        $this->assertStringContainsString('Apraksts', $body);
        $this->assertStringContainsString('Ņemiet līdzi masku.', $body);
        // A before-and-after of prose is noise, so the old text is not shown.
        $this->assertStringNotContainsString('Trīs dienu nometne.', $body);
    }

    public function test_each_member_reads_the_change_in_their_own_language(): void
    {
        $latvian = User::factory()->create(['locale' => 'lv', 'name' => 'Anna']);
        $german = User::factory()->create(['locale' => 'de', 'name' => 'Klaus']);

        $session = $this->makeSession();
        $this->register($latvian, $session);
        $this->register($german, $session);

        $this->update($session, ['location' => 'Cita zāle']);

        $bodies = app('mailer')->getSymfonyTransport()->messages()
            ->map(fn($sent) => $sent->getOriginalMessage()->getHtmlBody())
            ->all();

        $this->assertCount(2, $bodies);

        $latvianBody = collect($bodies)->first(fn($b) => str_contains($b, 'Anna'));
        $germanBody = collect($bodies)->first(fn($b) => str_contains($b, 'Klaus'));

        $this->assertStringContainsString('Izmaiņas pasākumā', $latvianBody);
        $this->assertStringContainsString('Vieta', $latvianBody);

        $this->assertStringContainsString('Eine Veranstaltung hat sich geändert', $germanBody);
        $this->assertStringContainsString('Ort', $germanBody);
        $this->assertStringContainsString('Sommerlager', $germanBody);
    }

    public function test_change_notices_carry_no_unsubscribe_link(): void
    {
        $session = $this->makeSession();
        $member = User::factory()->create(['locale' => 'lv']);
        $this->register($member, $session);

        $this->update($session, ['location' => 'Cita zāle']);

        $message = $this->lastMail();

        // Transactional: it follows from the member's own registration.
        $this->assertStringNotContainsString('/unsubscribe', $message->getHtmlBody());
        $this->assertNull($message->getHeaders()->get('List-Unsubscribe'));
    }

    public function test_a_deleted_session_quietly_drops_its_pending_change_notice(): void
    {
        Notification::fake();

        $session = $this->makeSession();
        $member = User::factory()->create();
        $this->register($member, $session);

        $id = $session->id;
        $session->delete();

        NotifyTrainingSessionChanged::dispatch($id, [
            ['field' => 'location', 'from' => 'A', 'to' => 'B'],
        ]);

        Notification::assertNothingSent();
    }

    public function test_the_plain_text_part_carries_the_same_changes(): void
    {
        $session = $this->makeSession();
        $member = User::factory()->create(['locale' => 'lv']);
        $this->register($member, $session);

        $this->update($session, ['location' => 'Sporta halle, Jelgava']);

        $text = $this->lastMail()->getTextBody();

        $this->assertStringContainsString('Vieta', $text);
        $this->assertStringContainsString('Ādmiņu iela 4, Rīga', $text);
        $this->assertStringContainsString('Sporta halle, Jelgava', $text);
    }
}
