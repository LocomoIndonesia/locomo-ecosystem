<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentMission extends Model
{
    protected $fillable = [
        'user_id',
        'mission_type',
        'status',
        'staking_amount',
        'reward_gold_amount',
        'business_name',
        'owner_email',
        'owner_phone',
        'staking_locked_at',
        'expires_at',
        'confirmed_at',
        'released_at',
    ];

    protected $casts = [
        'staking_locked_at' => 'datetime',
        'expires_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'released_at' => 'datetime',
        'staking_amount' => 'decimal:2',
        'reward_gold_amount' => 'decimal:2',
    ];

    /**
     * Get the agent user that owns the mission.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
