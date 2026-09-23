<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'store_name',
        'logo',
        'address',
        'phone',
        'tax_enabled',
        'tax_percent',
        'receipt_footer',
        'paper_size',
        'qris_image',
    ];

    protected function casts(): array
    {
        return [
            'tax_enabled' => 'boolean',
            'tax_percent' => 'decimal:2',
        ];
    }

    public static function current(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'store_name' => 'POSIFY Coffee House',
                'tax_enabled' => false,
                'tax_percent' => 0,
                'receipt_footer' => 'Terima kasih.',
                'paper_size' => '80mm',
            ]
        );
    }
}
