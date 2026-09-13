<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReputationLog extends Model
{
    protected $fillable = [
        'giver_id',
        'receiver_id',
        'action_type',
        'point_change',
        'description',
    ];

    public function giver()
    {
        return $this->belongsTo(User::class, 'giver_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
