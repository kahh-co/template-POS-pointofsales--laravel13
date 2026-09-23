<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $fillable = [
        'invoice_no',
        'user_id',
        'subtotal',
        'discount',
        'tax',
        'total',
        'payment_method',
        'payment_amount',
        'change_amount',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    protected function formattedTotal(): Attribute
    {
        return Attribute::get(fn () => 'Rp ' . number_format($this->total, 0, ',', '.'));
    }
}
