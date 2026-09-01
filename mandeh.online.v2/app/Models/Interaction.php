<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interaction extends Model
{
    protected $fillable = [
        'user_id',
        'entity_id',
        'type',
        'content',
        'rating_score',
        'status',
        'gps_lat',
        'gps_lng',
        'payload_json',
        'device_fingerprint',
    ];

    protected $casts = [
        'payload_json' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }
}
