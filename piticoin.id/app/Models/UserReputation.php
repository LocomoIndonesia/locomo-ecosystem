<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserReputation extends Model
{
    protected $fillable = [
        'user_id',
        'base_score',
        'network_score',
        'transaction_score',
        'checkin_score',
        'mission_score',
        'total_score',
        'current_status',
        'is_suspended',
    ];

    protected $casts = [
        'is_suspended' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
