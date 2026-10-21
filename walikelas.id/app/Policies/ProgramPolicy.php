<?php

namespace App\Policies;

use App\Models\LearningProgram;
use App\Models\User;

class ProgramPolicy
{
    /**
     * Memeriksa apakah user adalah Pendidik Pengampu Program yang aktif (TI-02)
     */
    public function manage(?User $user, LearningProgram $program): bool
    {
        if (!$user) {
            return false;
        }

        return $program->educators()
            ->where('user_id', $user->id)
            ->whereNull('ended_at')
            ->exists();
    }

    /**
     * Pendidik aktif dapat melihat detail program
     */
    public function view(?User $user, LearningProgram $program): bool
    {
        return $this->manage($user, $program);
    }

    /**
     * Pendidik aktif dapat mengupdate program
     */
    public function update(?User $user, LearningProgram $program): bool
    {
        return $this->manage($user, $program);
    }

    /**
     * Pendidik hanya dapat mengubah data jika program belum berstatus completed/archived (TAT-15)
     */
    public function canMutate(?User $user, LearningProgram $program): bool
    {
        if (!$this->manage($user, $program)) {
            return false;
        }

        return !in_array($program->status, ['completed', 'archived']);
    }

    /**
     * Memeriksa apakah wali sah berhak melihat progres anak di program tertentu (TI-03)
     */
    public function viewLearnerProgress(?User $user, LearningProgram $program, string $learnerId): bool
    {
        if (!$user) {
            return false;
        }

        // 1. Jika dia guru pengampu program, izinkan
        if ($this->manage($user, $program)) {
            return true;
        }

        // 2. Jika wali: wajib verified + anak terdaftar di program + consent aktif
        return $user->guardianRelations()
            ->where('learner_id', $learnerId)
            ->where('status', 'verified')
            ->whereNull('revoked_at')
            ->whereHas('consents', function ($query) use ($program) {
                $query->where('program_id', $program->id)
                      ->where('purpose_scope', 'PROGRAM_PARTICIPATION')
                      ->whereNull('revoked_at');
            })
            ->exists();
    }
}
