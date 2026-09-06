<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Tenant\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'price',
        'cost_price',
        'stock',
        'min_stock',
        'category',
        'image_url',
        'is_available',
        'is_published',
        'metadata',
    ];

    protected $casts = [
        'price' => 'integer',
        'cost_price' => 'integer',
        'stock' => 'integer',
        'min_stock' => 'integer',
        'is_available' => 'boolean',
        'is_published' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Relasi ke OrderItems.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relasi ke PurchaseItems.
     */
    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Update HPP dengan metode Moving Average saat kulakan barang baru.
     */
    public function updateMovingAverageCost(int $newQty, int $newBuyPrice): void
    {
        $currentStock = max(0, $this->stock);
        $currentCost = $this->cost_price > 0 ? $this->cost_price : $this->price;
        
        $totalStock = $currentStock + $newQty;
        if ($totalStock > 0) {
            $totalValue = ($currentStock * $currentCost) + ($newQty * $newBuyPrice);
            $newAverageCost = (int) round($totalValue / $totalStock);
            $this->cost_price = $newAverageCost;
        } else {
            $this->cost_price = $newBuyPrice;
        }

        $this->stock += $newQty;
        $this->save();
    }
}
