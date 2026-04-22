<?php

namespace App\Models;

use App\Traits\HasTenant;
use App\Traits\HasBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasTenant, HasBranch;

    protected $fillable = [
        'user_id', 
        'branch_id',
        'order_number', 
        'total_amount', 
        'payment_method', 
        'status', 
        'notes'
    ];

    public function user(): BelongsTo 
    { 
        return $this->belongsTo(User::class); 
    }

    public function branch(): BelongsTo 
    { 
        return $this->belongsTo(Branch::class); 
    }

    public function items(): HasMany 
    { 
        return $this->hasMany(OrderItem::class); 
    }
}
