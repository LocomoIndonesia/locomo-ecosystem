<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\AssignmentTransition;
use App\Models\AuditEvent;
use App\Models\Enrollment;
use App\Models\LearningProgram;
use App\Models\Mission;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;

class AssignmentService
{
    /**
     * Menerbitkan tugas misi belajar ke siswa (TI-04, TI-11, TAT-05)
     */
    public function assignMission(
        LearningProgram $program,
        Enrollment $enrollment,
        Mission $mission,
        User $educator,
        array $options = []
    ): Assignment {
        // TI-11: Rencana ajar wajib disahkan sebelum tugas dapat diterbitkan
        if (!$program->active_plan_version_id || !$program->activePlanVersion || $program->activePlanVersion->status !== 'approved') {
            throw new Exception('PLAN_NOT_APPROVED');
        }

        if ($enrollment->program_id !== $program->id) {
            throw new Exception('Enrollment does not belong to this program.');
        }

        // Home mission otomatis memerlukan konfirmasi wali
        $requiresGuardian = $mission->kind === 'home' || ($options['requires_guardian_confirmation'] ?? false);

        $assignment = Assignment::create([
            'id' => Str::uuid()->toString(),
            'program_id' => $program->id,
            'enrollment_id' => $enrollment->id,
            'mission_id' => $mission->id,
            'plan_version_id' => $program->active_plan_version_id, // TI-04: Terikat immutable ke versi rencana yang aktif saat diterbitkan
            'status' => 'assigned',
            'requires_guardian_confirmation' => $requiresGuardian,
            'requires_educator_review' => $options['requires_educator_review'] ?? true,
            'due_at' => $options['due_at'] ?? now()->addDays(3),
            'assigned_by' => $educator->id,
        ]);

        AssignmentTransition::create([
            'assignment_id' => $assignment->id,
            'from_status' => null,
            'to_status' => 'assigned',
            'actor_type' => 'educator',
            'actor_id' => $educator->id,
            'reason' => 'Mission assigned by educator',
            'created_at' => now(),
        ]);

        AuditEvent::log(
            actorType: 'user',
            actorId: $educator->id,
            action: 'ASSIGNMENT_CREATED',
            subjectType: 'assignment',
            subjectId: $assignment->id,
            programId: $program->id,
            payload: [
                'enrollment_id' => $enrollment->id,
                'mission_id' => $mission->id,
                'plan_version_id' => $program->active_plan_version_id,
            ]
        );

        return $assignment;
    }
}
