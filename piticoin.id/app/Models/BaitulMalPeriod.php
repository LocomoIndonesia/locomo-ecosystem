<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BaitulMalPeriod extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'period_name',
        'distribution_date',
        'total_gold_distributed',
        'beneficiaries_count',
        'status',
        'notes',
    ];

    protected $casts = [
        'distribution_date' => 'datetime',
        'total_gold_distributed' => 'integer',
        'beneficiaries_count' => 'integer',
    ];

    public function distributions(): HasMany
    {
        return $this->hasMany(BaitulMalDistribution::class, 'period_id');
    }
}
