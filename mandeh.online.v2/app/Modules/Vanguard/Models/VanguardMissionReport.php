<?php

declare(strict_types=1);

namespace App\Modules\Vanguard\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VanguardMissionReport extends BaseModel
{
    protected $fillable = [
        'participant_id',
        'mission_id',
        'status', // pending, approved, rejected
        'report_text',
        'proof_url',
        'points_awarded',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(VanguardParticipant::class, 'participant_id');
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(VanguardMission::class, 'mission_id');
    }
}
