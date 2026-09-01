<?php

declare(strict_types=1);

namespace App\Modules\Vanguard\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VanguardSurvey extends BaseModel
{
    protected $table = 'vanguard_surveys';

    protected $fillable = [
        'title',
        'description',
        'target_roles',
        'options',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'target_roles' => 'array',
        'options' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function votes(): HasMany
    {
        return $this->hasMany(VanguardVote::class, 'survey_id');
    }

    public function participations(): HasMany
    {
        return $this->hasMany(VanguardVoteParticipation::class, 'survey_id');
    }

    /**
     * Count the total eligible target users for this survey.
     */
    public function getEligibleUsersCount(): int
    {
        $roles = $this->target_roles ?? [];
        if (in_array('all', $roles)) {
            return \App\Models\User::count();
        }

        $query = \App\Models\User::query();
        $hasVanguardTarget = in_array('vanguard', $roles);
        $specificRoles = array_filter($roles, fn($r) => $r !== 'vanguard');

        $query->where(function ($q) use ($specificRoles, $hasVanguardTarget) {
            if (!empty($specificRoles)) {
                $q->whereIn('role', $specificRoles);
            }
            if ($hasVanguardTarget) {
                $q->orWhereHas('vanguardParticipant');
            }
        });

        return $query->count();
    }

    /**
     * Check if the voting has ended because the end time has passed
     * or all eligible users have voted.
     */
    public function isClosed(int $votedCount = null): bool
    {
        if (\Carbon\Carbon::now()->greaterThan($this->end_time)) {
            return true;
        }

        if ($votedCount === null) {
            $votedCount = $this->participations()->count();
        }

        $eligibleCount = $this->getEligibleUsersCount();
        return $eligibleCount > 0 && $votedCount >= $eligibleCount;
    }
}
