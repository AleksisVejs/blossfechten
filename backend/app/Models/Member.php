<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = ['full_name', 'role_title', 'rank', 'photo_url', 'bio', 'sort_order'];
    protected $casts = ['bio' => 'array'];
}
