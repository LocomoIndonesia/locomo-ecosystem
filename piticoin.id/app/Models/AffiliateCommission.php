<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateCommission extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'service_id',
        'service_order_id',
        'order_installment_id',
        'beneficiary_user_id',
        'upline_user_id',
        'level',
        'percentage',
        'amount_gold',
        'amount_fiat',
        'currency',
        'status', // 'SETTLED', 'PENDING'
        'ledger_id',
    ];

    protected $casts = [
        'level' => 'integer',
        'percentage' => 'decimal:2',
        'amount_gold' => 'decimal:2',
        'amount_fiat' => 'decimal:2',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(EcosystemService::class, 'service_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }

    public function installment(): BelongsTo
    {
        return $this->belongsTo(OrderInstallment::class, 'order_installment_id');
    }

    public function beneficiaryUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'beneficiary_user_id');
    }

    public function uplineUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'upline_user_id');
    }

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(Ledger::class, 'ledger_id');
    }
}
