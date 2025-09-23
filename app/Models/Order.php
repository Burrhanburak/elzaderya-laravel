<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\Book;
use App\Models\Poem;

class Order extends Model
{
    protected $fillable = [
        'email',
        'purchasable_type',
        'purchasable_id',
        'transaction_id',
        'status',
        'amount',
        'name',
        'address',
        'country',
        'postal_code',
        'email_sent',
        'email_sent_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'email_sent' => 'boolean',
        'email_sent_at' => 'datetime',
    ];

    public function purchasable(): MorphTo
    {
        return $this->morphTo();
    }
    
    // Helper method for getting the actual model
    public function getProduct()
    {
        if ($this->purchasable_type === 'book') {
            return Book::find($this->purchasable_id);
        } elseif ($this->purchasable_type === 'poem') {
            return Poem::find($this->purchasable_id);
        }
        return null;
    }
}
