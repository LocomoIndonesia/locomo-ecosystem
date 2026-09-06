<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'purchase_id',
        'product_id',
        'quantity',
        'buy_price',
        'subtotal',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'buy_price' => 'integer',
        'subtotal' => 'integer',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
