<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['slug', 'title', 'body', 'published'];
    protected $casts = [
        'title' => 'array',
        'body' => 'array',
        'published' => 'boolean',
    ];
}
