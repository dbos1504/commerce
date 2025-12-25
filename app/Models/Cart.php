<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function getTotalAttribute(): float
    {
        // Ensure items are loaded
        if (!$this->relationLoaded('items')) {
            $this->load('items.product');
        }

        return $this->items->sum(function ($item) {
            // Ensure product is loaded
            if (!$item->relationLoaded('product')) {
                $item->load('product');
            }
            return $item->quantity * (float) $item->product->price;
        });
    }
}
