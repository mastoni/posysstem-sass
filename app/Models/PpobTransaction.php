<?php

namespace App\Models;

use App\Traits\HasBranch;
use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpobTransaction extends Model
{
    use HasTenant, HasBranch;

    protected $fillable = [
        'user_id',
        'branch_id',
        'order_id',
        'ppob_product_code',
        'customer_number',
        'ref_id',
        'provider_ref_id',
        'amount',
        'status',
        'response_msg',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(PpobProduct::class, 'ppob_product_code', 'code');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
