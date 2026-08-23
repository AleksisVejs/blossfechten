<?php

namespace Tests\Feature;

use App\Jobs\AnnounceTrainingSession;
use App\Models\TrainingSession;
use App\Models\User;
use App\Notifications\NewEventNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NewEventNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'starts_at' => now()->addWeek()->format('Y-m-d\TH:i:s'),
            'ends_at' => now()->addWeek()->addHours(2)->format('Y-m-d\TH:i:s'),
            'location' => 'Ādmiņu iela 4, Rīga',
            'focus' => 'Longsword',
            'title' => ['lv' => 'Vasaras nometne', 'en' => 'Summer camp'],
            'description' => ['lv' => 'Trīs dienu nometne.', 'en' => 'Three day camp.'],
            'capacity' => 20,
        ], $overrides);
    }

    public function test_announcement_reaches_opted_in_verified_members_only(): void
    {
        Notification::fake();

        $subscriber = User::factory()->create(['notify_new_events' => true]);
        $optedOut = User::factory()->create(['notify_new_events' => false]);
        $unverified = User::factory()->unverified()->create(['notify_new_events' => true]);

        $this->actingAs($this->admin())
            ->postJson('/api/admin/trainings', $this->payload(['notify_subscribers' => true]))
            ->assertCreated();

        Notification::assertSentTo($subscriber, NewEventNotification::class);
        Notification::assertNotSentTo($optedOut, NewEventNotification::class);
        Notification::assertNotSentTo($unverified, NewEventNotification::class);
    }

    public function test_no_mail_goes_out_unless_the_admin_asks_for_it(): void
    {
        Notification::fake();

        $subscriber = User::factory()->create(['notify_new_events' => true]);

        $this->actingAs($this->admin())
            ->postJson('/api/admin/trainings', $this->payload())
            ->assertCreated();

        Notification::assertNothingSent();
        $this->assertNull(TrainingSession::first()->notified_at);
        $this->assertTrue($subscriber->fresh()->notify_new_events);
    }

    public function test_editing_an_announced_session_never_mails_the_club_again(): void
    {
        Notification::fake();

        $subscriber = User::factory()->create(['notify_new_events' => true]);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->postJson('/api/admin/trainings', $this->payload(['notify_subscribers' => true]))
            ->assertCreated();

        $session = TrainingSession::first();
        $this->assertNotNull($session->notified_at);

        $this->actingAs($admin)
            ->putJson("/api/admin/trainings/{$session->id}", $this->payload([
                'notify_subscribers' => true,
                'location' => 'Cita vieta',
            ]))
            ->assertOk();

        Notification::assertSentToTimes($subscriber, NewEventNotification::class, 1);
    }

    public function test_cancelled_and_past_sessions_are_never_announced(): void
    {
        Notification::fake();

        User::factory()->create(['notify_new_events' => true]);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->postJson('/api/admin/trainings', $this->payload([
                'notify_subscribers' => true,
                'cancelled' => true,
            ]))
            ->assertCreated();

        $this->actingAs($admin)
            ->postJson('/api/admin/trainings', $this->payload([
                'notify_subscribers' => true,
                'starts_at' => now()->subWeek()->format('Y-m-d\TH:i:s'),
                'ends_at' => now()->subWeek()->addHours(2)->format('Y-m-d\TH:i:s'),
            ]))
            ->assertCreated();

        Notification::assertNothingSent();
    }

    public function test_the_job_is_idempotent_when_it_runs_twice(): void
    {
        Notification::fake();

        User::factory()->create(['notify_new_events' => true]);
        $session = TrainingSession::create($this->payload());

        (new AnnounceTrainingSession($session->id))->handle();
        (new AnnounceTrainingSession($session->id))->handle();

        Notification::assertSentTimes(NewEventNotification::class, 1);
    }

    public function test_unsubscribe_token_stops_future_announcements(): void
    {
        $user = User::factory()->create(['notify_new_events' => true]);
        $token = $user->ensureUnsubscribeToken();

        $this->getJson("/api/notifications/unsubscribe/{$token}")
            ->assertOk()
            ->assertJsonPath('email', $user->email);

        $this->assertFalse($user->fresh()->notify_new_events);
    }

    public function test_unknown_unsubscribe_token_is_rejected(): void
    {
        $this->getJson('/api/notifications/unsubscribe/' . str_repeat('x', 64))
            ->assertNotFound();
    }

    public function test_unsubscribe_token_is_never_exposed_through_the_api(): void
    {
        $user = User::factory()->create();
        $user->ensureUnsubscribeToken();

        $this->actingAs($user)
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonMissingPath('user.unsubscribe_token');
    }

    public function test_each_member_is_mailed_in_their_own_language(): void
    {
        $latvian = User::factory()->create(['locale' => 'lv', 'notify_new_events' => true]);
        $german = User::factory()->create(['locale' => 'de', 'notify_new_events' => true]);

        $session = TrainingSession::create($this->payload());

        $lvMail = (new NewEventNotification($session))->toMail($latvian);
        $deMail = (new NewEventNotification($session))->toMail($german);

        $this->assertSame('Jauns pasākums: Vasaras nometne', $lvMail->subject);
        $this->assertSame('Neue Veranstaltung: Summer camp', $deMail->subject);
    }

    public function test_the_email_actually_renders_with_details_and_an_unsubscribe_link(): void
    {
        $user = User::factory()->create(['locale' => 'lv', 'notify_new_events' => true]);
        $session = TrainingSession::create($this->payload());

        // No Notification::fake() here on purpose: this drives the real Blade
        // templates through the array transport so a broken view fails loudly.
        $user->notify(new NewEventNotification($session));

        $messages = app('mailer')->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);

        $sent = $messages->first()->getOriginalMessage();
        $html = $sent->getHtmlBody();
        $text = $sent->getTextBody();
        $token = $user->fresh()->unsubscribe_token;

        foreach ([$html, $text] as $body) {
            $this->assertStringContainsString('Vasaras nometne', $body);
            $this->assertStringContainsString('Ādmiņu iela 4, Rīga', $body);
            $this->assertStringContainsString('Trīs dienu nometne.', $body);
            $this->assertStringContainsString($session->starts_at->format('d.m.Y H:i'), $body);
            $this->assertStringContainsString('/unsubscribe?token=' . $token, $body);
        }

        $this->assertStringContainsString('Jauns pasākums', $sent->getSubject());
        $this->assertStringContainsString(
            $token,
            $sent->getHeaders()->get('List-Unsubscribe')->getBodyAsString()
        );
    }

    public function test_announcements_are_sent_from_the_configured_club_address(): void
    {
        // DMARC on blossfechtenriga.com uses strict alignment, so the From
        // address has to stay on the club domain or announcements fail checks.
        config(['mail.from.address' => 'hello@blossfechtenriga.com']);
        config(['mail.from.name' => 'Blossfechten Riga']);

        $user = User::factory()->create(['locale' => 'lv', 'notify_new_events' => true]);
        $session = TrainingSession::create($this->payload());

        $user->notify(new NewEventNotification($session));

        $sent = app('mailer')->getSymfonyTransport()->messages()->first()->getOriginalMessage();
        $from = $sent->getFrom()[0];

        $this->assertSame('hello@blossfechtenriga.com', $from->getAddress());
        $this->assertSame('Blossfechten Riga', $from->getName());
    }

    public function test_members_can_opt_out_from_their_profile(): void
    {
        $user = User::factory()->create(['notify_new_events' => true]);

        $this->actingAs($user)
            ->putJson('/api/auth/profile', [
                'name' => $user->name,
                'locale' => 'lv',
                'notify_new_events' => false,
            ])
            ->assertOk();

        $this->assertFalse($user->fresh()->notify_new_events);
    }
}
