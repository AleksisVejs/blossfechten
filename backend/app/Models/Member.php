<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = ['full_name', 'role_title', 'rank', 'photo_url', 'bio', 'sort_order', 'is_instructor'];

    protected $casts = [
        'bio' => 'array',
        'is_instructor' => 'boolean',
        'sort_order' => 'integer',
    ];
}
