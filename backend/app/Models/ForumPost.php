<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumPost extends Model
{
    protected $fillable = [
        'title',
        'excerpt',
        'body',
        'slug',
        'cover_image_url',
        'tags',
        'is_pinned',
        'published',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'title' => 'array',
        'excerpt' => 'array',
        'body' => 'array',
        'tags' => 'array',
        'is_pinned' => 'boolean',
        'published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('published', true)
            ->where(function (Builder $inner) {
                $inner->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }
}
