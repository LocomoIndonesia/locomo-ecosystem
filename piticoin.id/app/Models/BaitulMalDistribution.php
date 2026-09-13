<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BaitulMalDistribution extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'period_id',
        'beneficiary_user_id',
        'masked_beneficiary_name',
        'beneficiary_category',
        'amount_gold',
        'distribution_note',
        'disbursed_at',
    ];

    protected $casts = [
        'amount_gold' => 'integer',
        'disbursed_at' => 'datetime',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(BaitulMalPeriod::class, 'period_id');
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(User::class, 'beneficiary_user_id');
    }
}
