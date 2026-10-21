<?php

namespace App\Policies;

use App\Models\Enrollment;
use App\Models\MasteryState;
use App\Models\User;

class MasteryStatePolicy
{
    /**
     * Memeriksa apakah user berhak memvalidasi status mastery anak (TI-06, TAT-19)
     */
    public function validateMastery(User $user, Enrollment $enrollment): bool
    {
        return $enrollment->program->educators()
            ->where('user_id', $user->id)
            ->whereNull('ended_at')
            ->exists();
    }
}
