<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceLicense extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'service_id',
        'user_id',
        'registered_by_id',
        'target_type',
        'beneficiary_data',
        'license_code',
        'status',
        'payment_method',
        'amount_paid',
        'currency',
        'expires_at',
        'external_reference_id',
        'metadata',
    ];

    protected $casts = [
        'beneficiary_data' => 'array',
        'metadata' => 'array',
        'amount_paid' => 'decimal:2',
        'expires_at' => 'datetime',
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
}
