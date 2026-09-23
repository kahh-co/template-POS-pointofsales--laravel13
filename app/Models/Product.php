<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'price',
        'stock',
        'minimum_stock',
        'image',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function transactionItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    protected function isLowStock(): Attribute
    {
        return Attribute::get(fn () => $this->stock <= $this->minimum_stock && $this->stock > 0);
    }

    protected function isOut(): Attribute
    {
        return Attribute::get(fn () => $this->stock <= 0);
    }

    public function scopeSearch(Builder $query, ?string $keyword): Builder
    {
        if ($keyword) {
            $query->where('name', 'like', "%{$keyword}%");
        }

        return $query;
    }
}
