<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaitulMalPool extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'total_collected_gold',
        'active_balance_gold',
        'total_distributed_gold',
        'total_donations_count',
        'total_beneficiaries_count',
    ];

    protected $casts = [
        'total_collected_gold' => 'integer',
        'active_balance_gold' => 'integer',
        'total_distributed_gold' => 'integer',
        'total_donations_count' => 'integer',
        'total_beneficiaries_count' => 'integer',
    ];
}
