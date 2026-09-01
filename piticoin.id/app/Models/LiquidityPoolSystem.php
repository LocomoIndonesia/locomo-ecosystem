<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiquidityPoolSystem extends Model
{
    protected $table = 'liquidity_pool_system';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'total_fiat_in_bank',
        'total_gold_in_circulation',
    ];

    protected $casts = [
        'total_fiat_in_bank' => 'integer',
        'total_gold_in_circulation' => 'integer',
    ];
}
