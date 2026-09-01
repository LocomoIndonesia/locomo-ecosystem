<?php

declare(strict_types=1);

namespace App\Modules\Vanguard\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VanguardMission extends BaseModel
{
    protected $fillable = [
        'title',
        'description',
        'armada_type', // darat_laut, udara, both
        'points_reward',
        'start_time',
        'end_time',
        'bonus_points',
        'badge_id',
        'admin_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function reports(): HasMany
    {
        return $this->hasMany(VanguardMissionReport::class, 'mission_id');
    }
}
