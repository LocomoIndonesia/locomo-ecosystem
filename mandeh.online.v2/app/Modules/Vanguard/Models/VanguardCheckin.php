<?php

declare(strict_types=1);

namespace App\Modules\Vanguard\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VanguardCheckin extends BaseModel
{
    protected $fillable = [
        'participant_id',
        'checkin_date',
        'verified_method', // face_to_face or livestream
    ];

    protected $casts = [
        'checkin_date' => 'date',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(VanguardParticipant::class, 'participant_id');
    }
}
