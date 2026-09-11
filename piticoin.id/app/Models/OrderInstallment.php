<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderInstallment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'service_order_id',
        'installment_number',
        'amount_gold',
        'amount_fiat',
        'currency', // 'GOLD' or 'IDR'
        'payment_method', // 'GOLD' or 'FIAT'
        'status', // 'PENDING', 'PAID', 'FAILED'
        'commissions_distributed',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'amount_gold' => 'decimal:2',
        'amount_fiat' => 'decimal:2',
        'commissions_distributed' => 'boolean',
        'paid_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class, 'order_installment_id');
    }
}
