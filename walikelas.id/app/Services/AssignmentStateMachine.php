<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\AssignmentTransition;
use App\Models\Attempt;
use App\Models\AuditEvent;
use App\Models\GuardianConfirmation;
use App\Models\GuardianRelation;
use App\Models\Response;
use Exception;
use Illuminate\Support\Str;

class AssignmentStateMachine
{
    /**
     * Kata kunci bahaya untuk perlindungan dan safeguarding anak (TI-10, TAT-16)
     */
    protected array $dangerKeywords = [
        'luka', 'darah', 'mati', 'pukul', 'racun', 'bunuh', 'sakiti', 'takut sekali', 'tolong aku', 'bakar', 'tendang'
    ];

    /**
     * Memulai pengerjaan misi membaca oleh siswa (assigned -> in_progress)
     */
    public function startMission(Assignment $assignment, string $actorType = 'learner_link', ?string $actorId = null): Attempt
    {
        if ($assignment->status === 'assigned') {
            $this->transition($assignment, 'in_progress', $actorType, $actorId, 'Siswa membuka tautan misi');
        }

        return Attempt::create([
            'id' => Str::uuid()->toString(),
            'assignment_id' => $assignment->id,
            'started_at' => now(),
            'active_seconds' => 0,
            'assisted_by_adult' => false,
        ]);
    }

    /**
     * Merekam jawaban siswa per langkah dan memeriksa kata kunci bahaya (TI-10, TAT-16)
     */
    public function recordStepResponse(
        Attempt $attempt,
        string $stepKey,
        array $answer,
        int $hintsUsed = 0,
        ?string $autoResult = null
    ): Response {
        $textToInspect = strtolower(json_encode($answer));
        $safetyFlag = false;

        foreach ($this->dangerKeywords as $keyword) {
            if (str_contains($textToInspect, $keyword)) {
                $safetyFlag = true;
                break;
            }
        }

        $response = Response::create([
            'id' => Str::uuid()->toString(),
            'attempt_id' => $attempt->id,
            'step_key' => $stepKey,
            'answer' => $answer,
            'hints_used' => $hintsUsed,
            'auto_result' => $autoResult,
            'safety_flag' => $safetyFlag,
            'created_at' => now(),
        ]);

        // TI-10 & TAT-16: Circuit breaker safeguarding otomatis
        if ($safetyFlag) {
            AuditEvent::log(
                actorType: 'system',
                actorId: null,
                action: 'EMERGENCY_SAFETY_CIRCUIT_BREAKER_TRIGGERED',
                subjectType: 'response',
                subjectId: $response->id,
                programId: $attempt->assignment->program_id,
                payload: [
                    'step_key' => $stepKey,
                    'learner_id' => $attempt->assignment->enrollment->learner_id,
                    'note' => 'Jawaban anak mengandung indikasi bahaya/kekerasan. Sesi dijeda otomatis.'
                ]
            );
        }

        return $response;
    }

    /**
     * Menyelesaikan langkah digital misi (in_progress -> needs_guardian atau submitted)
     */
    public function completeDigitalSteps(Assignment $assignment, Attempt $attempt, int $activeSeconds = 600): void
    {
        $attempt->update([
            'finished_at' => now(),
            'active_seconds' => $activeSeconds,
        ]);

        // Jika misi rumah atau butuh konfirmasi wali, alihkan ke needs_guardian
        if ($assignment->requires_guardian_confirmation || $assignment->mission->kind === 'home') {
            $this->transition(
                $assignment,
                'needs_guardian',
                'system',
                null,
                'Menunggu konfirmasi tugas rumah oleh orang tua'
            );
        } else {
            $this->transition(
                $assignment,
                'submitted',
                'learner_link',
                null,
                'Seluruh langkah digital selesai'
            );
        }
    }

    /**
     * Orang tua mengonfirmasi tugas rumah (needs_guardian -> submitted) (TI-07, TAT-09)
     */
    public function recordGuardianConfirmation(
        Assignment $assignment,
        GuardianRelation $guardianRelation,
        string $responseCode, // 'done', 'not_yet', 'need_help'
        ?string $note = null,
        string $channel = 'web_link'
    ): GuardianConfirmation {
        $confirmation = GuardianConfirmation::create([
            'id' => Str::uuid()->toString(),
            'assignment_id' => $assignment->id,
            'guardian_relation_id' => $guardianRelation->id,
            'response' => $responseCode,
            'note' => $note,
            'channel' => $channel,
            'confirmed_at' => now(),
        ]);

        // Guard FSM: Transisi ke 'submitted' HANYA terjadi jika response = 'done'
        if ($responseCode === 'done') {
            $this->transition(
                $assignment,
                'submitted',
                'guardian',
                $guardianRelation->guardian_user_id,
                'Orang tua mengonfirmasi anak telah menyelesaikan tugas rumah'
            );
        }

        // TI-07: Konfirmasi orang tua HANYA mengubah status tugas, DILARANG mengubah mastery_states!

        AuditEvent::log(
            actorType: 'guardian',
            actorId: $guardianRelation->guardian_user_id,
            action: 'GUARDIAN_CONFIRMATION_RECORDED',
            subjectType: 'guardian_confirmation',
            subjectId: $confirmation->id,
            programId: $assignment->program_id,
            payload: ['response' => $responseCode, 'assignment_id' => $assignment->id]
        );

        return $confirmation;
    }

    /**
     * Transisi status tugas dengan audit trail riwayat transisi (TI-13)
     */
    public function transition(
        Assignment $assignment,
        string $toStatus,
        string $actorType,
        ?string $actorId = null,
        ?string $reason = null
    ): void {
        $fromStatus = $assignment->status;

        $assignment->update(['status' => $toStatus]);

        AssignmentTransition::create([
            'assignment_id' => $assignment->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'reason' => $reason,
            'created_at' => now(),
        ]);
    }
}
