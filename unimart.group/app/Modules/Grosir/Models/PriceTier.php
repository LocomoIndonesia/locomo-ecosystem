<?php

namespace App\Modules\Grosir\Models;

use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'min_quantity',
        'price_per_unit',
        'unit_name',
    ];

    protected $casts = [
        'min_quantity' => 'integer',
        'price_per_unit' => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
