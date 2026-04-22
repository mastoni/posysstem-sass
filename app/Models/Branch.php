<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = [
        'user_id',
        'name',
        'address',
        'phone',
        'settings',
        'is_main',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_main' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
    
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_branches')
            ->withPivot('stock', 'price')
            ->withTimestamps();
    }
}
