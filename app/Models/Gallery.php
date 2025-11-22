<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    protected $fillable = [
        'title',
        'description',
        'images',
        'tags',
        'is_active',
    ];

    protected $casts = [
        'images' => 'array',
        'tags' => 'array',
        'is_active' => 'boolean',
    ];
}
