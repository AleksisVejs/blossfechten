<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'locale', 'phone', 'rank',
        'notify_new_events',
    ];

    protected $hidden = ['password', 'remember_token', 'unsubscribe_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notify_new_events' => 'boolean',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function trainings(): BelongsToMany
    {
        return $this->belongsToMany(TrainingSession::class, 'registrations')
            ->withPivot('status', 'note')
            ->withTimestamps();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Users who should receive announcements about newly published events.
     * Unverified addresses are skipped so announcements never become the
     * first mail a bad address receives.
     */
    public function scopeEventSubscribers(Builder $query): Builder
    {
        return $query->where('notify_new_events', true)
            ->whereNotNull('email_verified_at');
    }

    public function ensureUnsubscribeToken(): string
    {
        if (blank($this->unsubscribe_token)) {
            $this->forceFill(['unsubscribe_token' => Str::random(64)])->save();
        }

        return $this->unsubscribe_token;
    }
}
