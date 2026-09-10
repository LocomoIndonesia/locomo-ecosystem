<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UserCheckin extends Model
{
    use HasUuids;

    protected $table = 'user_checkins';

    protected $fillable = [
        'user_id',
        'checkin_date',
        'checkin_time',
        'time_window',
        'streak_count',
        'silver_reward_claimed',
        'xp_reward_claimed',
        'gold_bonus_claimed',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'checkin_date' => 'date',
        'streak_count' => 'integer',
        'silver_reward_claimed' => 'integer',
        'xp_reward_claimed' => 'integer',
        'gold_bonus_claimed' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
