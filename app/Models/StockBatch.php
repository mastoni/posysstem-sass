<?php

namespace App\Models;

use App\Traits\HasBranch;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBatch extends Model
{
    use HasFactory, HasTenant, HasBranch;

    protected $fillable = [
        'user_id',
        'branch_id',
        'product_id',
        'quantity_initial',
        'quantity_remaining',
        'cost_price',
        'batch_number',
        'expired_at',
    ];

    protected $casts = [
        'expired_at' => 'date',
        'cost_price' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
