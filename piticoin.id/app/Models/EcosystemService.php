<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EcosystemService extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'owner_id',
        'name',
        'code',
        'icon',
        'category',
        'service_type', // 'EXTERNAL' or 'INTERNAL'
        'description',
        'wizard_schema',
        'price_gold',
        'price_fiat',
        'service_url',
        'webhook_url',
        'status',
        'is_active',
        'allows_installments',
        'min_down_payment_gold',
        'min_down_payment_fiat',
        'commission_rules',
    ];

    protected $casts = [
        'wizard_schema' => 'array',
        'price_gold' => 'integer',
        'price_fiat' => 'decimal:2',
        'is_active' => 'boolean',
        'allows_installments' => 'boolean',
        'min_down_payment_gold' => 'decimal:2',
        'min_down_payment_fiat' => 'decimal:2',
        'commission_rules' => 'array',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(ServiceLicense::class, 'service_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class, 'service_id');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class, 'service_id');
    }
}
