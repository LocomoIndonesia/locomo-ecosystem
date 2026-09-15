<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionVerificationJuror extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'mission_verification_jurors';

    protected $fillable = [
        'verification_id',
        'juror_user_id',
        'vote',
        'stake_amount_silver',
        'reward_amount_silver',
        'notes',
        'voted_at',
        'is_slashed',
    ];

    protected $casts = [
        'stake_amount_silver' => 'integer',
        'reward_amount_silver' => 'integer',
        'is_slashed' => 'boolean',
        'voted_at' => 'datetime',
    ];

    public function verification(): BelongsTo
    {
        return $this->belongsTo(MissionVerification::class, 'verification_id');
    }

    public function juror(): BelongsTo
    {
        return $this->belongsTo(User::class, 'juror_user_id');
    }
}
