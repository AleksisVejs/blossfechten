<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingSession extends Model
{
    protected $fillable = [
        'starts_at', 'ends_at', 'location', 'focus',
        'title', 'description', 'capacity', 'members_only', 'cancelled',
    ];

    protected $casts = [
        'starts_at' => 'datetime:Y-m-d\TH:i:s',
        'ends_at' => 'datetime:Y-m-d\TH:i:s',
        'title' => 'array',
        'description' => 'array',
        'members_only' => 'boolean',
        'cancelled' => 'boolean',
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

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d\TH:i:s');
    }
}
