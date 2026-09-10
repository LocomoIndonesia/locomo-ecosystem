<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Quest extends Model
{
    use HasUuids;

    protected $table = 'quests';

    protected $fillable = [
        'title',
        'description',
        'stake_amount_silver',
        'reward_amount_gold',
        'time_limit_hours',
        'is_active',
        'quest_category',
        'target_persona',
        'time_window',
        'min_level',
        'parameters',
    ];

    protected $casts = [
        'stake_amount_silver' => 'integer',
        'reward_amount_gold' => 'integer',
        'time_limit_hours' => 'integer',
        'is_active' => 'boolean',
        'min_level' => 'integer',
        'parameters' => 'array',
    ];

    public function escrowTransactions()
    {
        return $this->hasMany(EscrowTransaction::class, 'quest_id');
    }
}
