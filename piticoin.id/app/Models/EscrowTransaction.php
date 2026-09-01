<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class EscrowTransaction extends Model
{
    use HasUuids;

    protected $table = 'escrow_transactions';

    protected $fillable = [
        'user_id',
        'quest_id',
        'staked_silver',
        'status', // LOCKED, SUCCESS, FAILED
        'expires_at',
    ];

    protected $casts = [
        'staked_silver' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function quest()
    {
        return $this->belongsTo(Quest::class, 'quest_id');
    }
}
