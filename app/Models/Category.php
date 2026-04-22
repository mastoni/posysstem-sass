<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasTenant;

    protected $fillable = ['user_id', 'name', 'slug'];

    public function user(): BelongsTo 
    { 
        return $this->belongsTo(User::class); 
    }

    public function products(): HasMany 
    { 
        return $this->hasMany(Product::class); 
    }
}
