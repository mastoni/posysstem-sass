<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpobProduct extends Model
{
    protected $fillable = ['code', 'name', 'category', 'brand', 'price_buy', 'price_sell', 'provider', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'price_buy' => 'decimal:2',
        'price_sell' => 'decimal:2',
    ];
}
