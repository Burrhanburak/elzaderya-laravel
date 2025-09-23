<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'file_url',
        'file_url_filename',
        'language',
    ];
}
