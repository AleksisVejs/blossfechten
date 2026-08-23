<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use App\Notifications\VerifyEmailNotification;
use Tests\TestCase;

class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('');
    }

    private function registration(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Mallory',
            'email' => 'someone@blossfechten.lv',
            'password' => 'attacker-password-123',
            'password_confirmation' => 'attacker-password-123',
        ], $overrides);
    }

    public function test_registering_over_an_unverified_admin_cannot_take_the_account(): void
    {
        // Exactly the shape `php artisan user:create` used to leave behind.
        $admin = User::factory()->unverified()->create([
            'email' => 'admin@blossfechten.lv',
            'name' => 'Club Administrator',
            'password' => Hash::make('the-real-admin-password'),
            'role' => 'admin',
        ]);

        $this->postJson('/api/auth/register', $this->registration([
            'email' => 'admin@blossfechten.lv',
        ]))->assertStatus(422)->assertJsonValidationErrors('email');

        $after = $admin->fresh();

        $this->assertSame('Club Administrator', $after->name);
        $this->assertTrue(Hash::check('the-real-admin-password', $after->password));
        $this->assertFalse(Hash::check('attacker-password-123', $after->password));
        $this->assertGuest();

        // And the caller has no admin session to spend.
        $this->getJson('/api/admin/users')->assertStatus(401);
    }

    public function test_registering_over_a_verified_account_is_still_refused(): void
    {
        User::factory()->create(['email' => 'member@blossfechten.lv']);

        $this->postJson('/api/auth/register', $this->registration([
            'email' => 'member@blossfechten.lv',
        ]))->assertStatus(422)->assertJsonValidationErrors('email');

        $this->assertGuest();
    }

    public function test_a_genuinely_new_address_still_registers_normally(): void
    {
        Notification::fake();

        $this->postJson('/api/auth/register', $this->registration())
            ->assertCreated()
            ->assertJsonPath('user.email', 'someone@blossfechten.lv');

        $user = User::where('email', 'someone@blossfechten.lv')->firstOrFail();

        $this->assertSame('member', $user->role);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_stranded_registrations_can_ask_for_a_fresh_verification_mail(): void
    {
        Notification::fake();

        $pending = User::factory()->unverified()->create(['email' => 'pending@blossfechten.lv']);

        $this->postJson('/api/auth/email/verify/resend', ['email' => 'pending@blossfechten.lv'])
            ->assertOk();

        Notification::assertSentTo($pending, VerifyEmailNotification::class);

        // The escape hatch must not become the hole it replaced.
        $this->assertGuest();
        $this->assertTrue(Hash::check('password', $pending->fresh()->password));
    }

    public function test_the_resend_endpoint_reveals_nothing_and_mails_nobody_else(): void
    {
        Notification::fake();

        $verified = User::factory()->create(['email' => 'verified@blossfechten.lv']);

        $known = $this->postJson('/api/auth/email/verify/resend', ['email' => 'verified@blossfechten.lv']);
        $unknown = $this->postJson('/api/auth/email/verify/resend', ['email' => 'nobody@blossfechten.lv']);

        $known->assertOk();
        $unknown->assertOk();
        $this->assertSame($known->json('message'), $unknown->json('message'));

        // Already verified, so there is nothing to re-send.
        Notification::assertNotSentTo($verified, VerifyEmailNotification::class);
    }

    public function test_login_is_rate_limited(): void
    {
        User::factory()->create([
            'email' => 'member@blossfechten.lv',
            'password' => Hash::make('the-real-password'),
        ]);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson('/api/auth/login', [
                'email' => 'member@blossfechten.lv',
                'password' => "guess-{$attempt}",
            ])->assertStatus(422);
        }

        // The sixth attempt is refused by the limiter, not the credentials —
        // so even the correct password is turned away.
        $this->postJson('/api/auth/login', [
            'email' => 'member@blossfechten.lv',
            'password' => 'the-real-password',
        ])->assertStatus(429);
    }

    public function test_registration_is_rate_limited(): void
    {
        Notification::fake();

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson('/api/auth/register', $this->registration([
                'email' => "burner{$attempt}@blossfechten.lv",
            ]))->assertCreated();
        }

        $this->postJson('/api/auth/register', $this->registration([
            'email' => 'burner6@blossfechten.lv',
        ]))->assertStatus(429);
    }
}
