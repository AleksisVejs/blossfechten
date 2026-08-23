<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class TrainingSession extends Model
{
    protected $fillable = [
        'starts_at', 'ends_at', 'location', 'focus',
        'title', 'description', 'capacity', 'members_only', 'cancelled',
        'send_reminder',
    ];

    protected $casts = [
        'starts_at' => 'datetime:Y-m-d\TH:i:s',
        'ends_at' => 'datetime:Y-m-d\TH:i:s',
        'title' => 'array',
        'description' => 'array',
        'members_only' => 'boolean',
        'cancelled' => 'boolean',
        'notified_at' => 'datetime',
        'send_reminder' => 'boolean',
        'reminded_at' => 'datetime',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'registrations')
            ->withPivot('status', 'note')
            ->withTimestamps();
    }

    public function confirmedCount(): int
    {
        return $this->registrations()->where('status', 'confirmed')->count();
    }

    public function isFull(): bool
    {
        return $this->confirmedCount() >= $this->capacity;
    }

    /**
     * Hand every free seat to the members who have been waiting longest.
     *
     * Callers must hold a lock on this row (see TrainingController::unregister)
     * — the free-seat count is read and acted on separately, so two concurrent
     * releases would otherwise promote past the capacity.
     *
     * @return \Illuminate\Support\Collection<int, Registration> promoted rows, oldest first
     */
    public function promoteFromWaitlist(): Collection
    {
        $free = $this->capacity - $this->confirmedCount();

        if ($free <= 0) {
            return collect();
        }

        $promoted = $this->registrations()
            ->where('status', 'waitlist')
            ->orderBy('created_at')
            ->orderBy('id')
            ->limit($free)
            ->get();

        foreach ($promoted as $registration) {
            $registration->update(['status' => 'confirmed']);
        }

        return $promoted;
    }

    public function wasAnnounced(): bool
    {
        return $this->notified_at !== null;
    }

    /**
     * A session is only worth announcing while it is still live and upcoming —
     * never a cancelled one, and never one that has already finished.
     */
    public function isAnnounceable(): bool
    {
        return ! $this->wasAnnounced()
            && ! $this->cancelled
            && $this->starts_at !== null
            && $this->starts_at->isFuture();
    }

    public function markAnnounced(): void
    {
        $this->forceFill(['notified_at' => now()])->save();
    }

    public function wasReminded(): bool
    {
        return $this->reminded_at !== null;
    }

    /**
     * Sessions whose reminder is due: opted in, not yet reminded, still live,
     * and starting within the next day.
     *
     * The notified_at clause avoids the daft case where an admin creates an
     * event less than a day before it starts and ticks both boxes — without it
     * the reminder chases the announcement out the door minutes later.
     */
    public function scopeDueForReminder(Builder $query): Builder
    {
        return $query->where('send_reminder', true)
            ->whereNull('reminded_at')
            ->where('cancelled', false)
            ->where('starts_at', '>', now())
            ->where('starts_at', '<=', now()->addDay())
            ->where(function (Builder $q) {
                $q->whereNull('notified_at')
                    ->orWhere('notified_at', '<=', now()->subHour());
            });
    }

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d\TH:i:s');
    }
}
