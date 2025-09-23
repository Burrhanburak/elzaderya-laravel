<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'cover_image',
        'preview_pdf',
        'full_pdf',
        'cover_image_filename',
        'preview_pdf_filename',
        'full_pdf_filename',
        'price',
        'paddle_product_id',
        'paddle_price_id',
        'language',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];
}
