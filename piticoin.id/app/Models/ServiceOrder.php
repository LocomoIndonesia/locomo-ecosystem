<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceOrder extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'service_id',
        'user_id',
        'registered_by_id',
        'license_code',
        'package_tier',
        'payment_type', // 'FULL' or 'INSTALLMENT'
        'total_amount_gold',
        'total_amount_fiat',
        'paid_amount_gold',
        'paid_amount_fiat',
        'status', // 'PENDING', 'ACTIVE', 'COMPLETED', 'CANCELLED'
        'order_data',
    ];

    protected $casts = [
        'total_amount_gold' => 'decimal:2',
        'total_amount_fiat' => 'decimal:2',
        'paid_amount_gold' => 'decimal:2',
        'paid_amount_fiat' => 'decimal:2',
        'order_data' => 'array',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(EcosystemService::class, 'service_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by_id');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(OrderInstallment::class, 'service_order_id')->orderBy('installment_number');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class, 'service_order_id');
    }

    public function getRemainingGoldAttribute(): float
    {
        return max(0, (float) $this->total_amount_gold - (float) $this->paid_amount_gold);
    }

    public function getRemainingFiatAttribute(): float
    {
        return max(0, (float) $this->total_amount_fiat - (float) $this->paid_amount_fiat);
    }
}
