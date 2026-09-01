<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Tenant\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WastedItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'reason',
        'original_status',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
