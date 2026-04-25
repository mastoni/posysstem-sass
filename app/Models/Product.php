<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasTenant;

    protected $fillable = ['user_id', 'category_id', 'name', 'slug', 'sku', 'price', 'cost_price', 'stock', 'image', 'is_active', 'description'];

    public function user(): BelongsTo 
    { 
        return $this->belongsTo(User::class); 
    }

    public function category(): BelongsTo 
    { 
        return $this->belongsTo(Category::class); 
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'product_branches')
            ->withPivot('stock', 'price')
            ->withTimestamps();
    }
}
