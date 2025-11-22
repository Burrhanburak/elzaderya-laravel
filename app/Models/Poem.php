<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Poem extends Model
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
        'currency',
        'lemon_variant_id',
        'language',
    ];

   
    
    // Accessor for cover image URL
    public function getCoverImageUrlAttribute()
    {
        if ($this->cover_image) {
            return Storage::disk('s3')->url($this->cover_image);
        }
        return null;
    }
    
    // Accessor for preview PDF URL
    public function getPreviewPdfUrlAttribute()
    {
        if ($this->preview_pdf) {
            return Storage::disk('s3')->url($this->preview_pdf);
        }
        return null;
    }
    
    // Accessor for full PDF URL
    public function getFullPdfUrlAttribute()
    {
        if ($this->full_pdf) {
            return Storage::disk('s3')->url($this->full_pdf);
        }
        return null;
    }
}
