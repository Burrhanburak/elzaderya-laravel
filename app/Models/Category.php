<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = [
        'name_tr',
        'name_en', 
        'name_ru',
        'name_az',
        'slug',
        'color',
    ];

    protected $casts = [
        'name_tr' => 'string',
        'name_en' => 'string',
        'name_ru' => 'string',
        'name_az' => 'string',
    ];

    public function blogs(): BelongsToMany
    {
        return $this->belongsToMany(Blog::class, 'blog_categories');
    }

    public function getNameAttribute(): string
    {
        $locale = app()->getLocale();
        return $this->{"name_{$locale}"} ?? $this->name_tr ?? $this->name_en ?? 'No Name';
    }
}