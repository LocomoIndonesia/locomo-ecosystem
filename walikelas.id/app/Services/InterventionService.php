<?php

namespace App\Services;

use App\Models\AiRun;
use App\Models\AiSuggestion;
use App\Models\AuditEvent;
use App\Models\Competency;
use App\Models\EducatorDecision;
use App\Models\Enrollment;
use App\Models\Intervention;
use App\Models\Response as StudentResponse;
use App\Models\User;
use Illuminate\Support\Str;

class InterventionService
{
    public function __construct(
        protected GeminiService $geminiService
    ) {}

    /**
     * Menganalisis kesenjangan belajar siswa (TI-05, TAT-10)
     */
    public function analyzeGaps(Enrollment $enrollment): array
    {
        $assignments = $enrollment->assignments()->pluck('id');
        $hasResponses = StudentResponse::whereIn('assignment_id', $assignments)->exists();
        $hasMastery = $enrollment->masteryStates()->exists();

        // TAT-10: Analisis kesenjangan tanpa bukti asesmen wajib mengembalikan uncertainty: insufficient_evidence
        if (!$hasResponses && !$hasMastery) {
            return [
                'uncertainty' => 'insufficient_evidence',
                'rationale' => 'Belum ada bukti data respons atau observasi untuk siswa ini.',
                'gaps' => [],
                'suggestion' => null,
            ];
        }

        $result = $this->geminiService->generateStructured(
            purpose: 'gap_analysis',
            systemPrompt: 'Analisis kesenjangan belajar siswa berdasarkan bukti asesmen formatif.',
            userPrompt: "Siswa ID: {$enrollment->learner_id}, Program ID: {$enrollment->program_id}",
            jsonSchema: [
                'type' => 'object',
                'properties' => [
                    'uncertainty' => ['type' => 'string'],
                    'identified_gaps' => ['type' => 'array', 'items' => ['type' => 'string']],
                    'suggested_focus' => ['type' => 'string'],
                ],
                'required' => ['uncertainty', 'identified_gaps', 'suggested_focus'],
            ],
            programId: $enrollment->program_id,
            inputRefs: ['enrollment_id' => $enrollment->id]
        );

        return $result;
    }

    /**
     * Copilot mengusulkan rekomendasi intervensi (TI-05)
     */
    public function proposeIntervention(
        Enrollment $enrollment,
        Competency $competency,
        string $description,
        string $rationale,
        string $uncertainty = 'low'
    ): AiSuggestion {
        $aiRunId = Str::uuid()->toString();
        AiRun::create([
            'id' => $aiRunId,
            'purpose' => 'intervention',
            'program_id' => $enrollment->program_id,
            'prompt_version' => 'v1.0',
            'model_name' => 'gemini-1.5-flash',
            'input_refs' => ['enrollment_id' => $enrollment->id, 'competency_id' => $competency->id],
            'output_payload' => [
                'competency_code' => $competency->code,
                'description' => $description,
                'rationale' => $rationale,
            ],
            'status' => 'ok',
            'latency_ms' => 120,
            'created_at' => now(),
        ]);

        return AiSuggestion::create([
            'id' => Str::uuid()->toString(),
            'ai_run_id' => $aiRunId,
            'program_id' => $enrollment->program_id,
            'enrollment_id' => $enrollment->id,
            'kind' => 'intervention',
            'payload' => [
                'competency_id' => $competency->id,
                'competency_code' => $competency->code,
                'description' => $description,
            ],
            'rationale' => $rationale,
            'uncertainty' => $uncertainty,
            'status' => 'pending',
            'created_at' => now(),
        ]);
    }

    /**
     * Keputusan mutlak guru atas usulan AI (Educator-in-the-Loop, TI-05, TAT-11)
     */
    public function handleEducatorDecision(
        User $educator,
        AiSuggestion $suggestion,
        string $decision,
        ?string $reason = null,
        ?array $editedPayload = null
    ): array {
        $decisionRecord = EducatorDecision::create([
            'id' => Str::uuid()->toString(),
            'suggestion_id' => $suggestion->id,
            'subject_type' => 'intervention',
            'subject_id' => $suggestion->id,
            'decision' => $decision,
            'reason' => $reason,
            'edited_payload' => $editedPayload,
            'decided_by' => $educator->id,
            'decided_at' => now(),
        ]);

        $newSuggestionStatus = match ($decision) {
            'approve' => 'accepted',
            'edit' => 'edited',
            default => 'rejected',
        };

        $suggestion->update(['status' => $newSuggestionStatus]);

        AuditEvent::log(
            actorType: 'educator',
            actorId: $educator->id,
            action: 'EDUCATOR_DECISION_' . strtoupper($decision),
            subjectType: 'ai_suggestion',
            subjectId: $suggestion->id,
            programId: $suggestion->program_id,
            payload: [
                'decision' => $decision,
                'reason' => $reason,
                'edited_payload' => $editedPayload,
            ]
        );

        // TAT-11 & TI-05: Jika ditolak, rekomendasi diabaikan; TIDAK ADA intervensi yang dibuat
        if ($decision === 'reject') {
            return [
                'status' => 'rejected',
                'decision' => $decisionRecord,
                'intervention' => null,
            ];
        }

        // Jika disetujui / diedit guru, baru record Intervensi dibuat
        $payload = $editedPayload ?: $suggestion->payload;
        $competencyId = $payload['competency_id'] ?? null;
        $description = $payload['description'] ?? $suggestion->rationale;

        $intervention = Intervention::create([
            'id' => Str::uuid()->toString(),
            'program_id' => $suggestion->program_id,
            'enrollment_id' => $suggestion->enrollment_id,
            'competency_id' => $competencyId,
            'description' => $description,
            'decision_id' => $decisionRecord->id,
            'status' => 'planned',
            'created_at' => now(),
        ]);

        AuditEvent::log(
            actorType: 'educator',
            actorId: $educator->id,
            action: 'INTERVENTION_CREATED_FROM_DECISION',
            subjectType: 'intervention',
            subjectId: $intervention->id,
            programId: $suggestion->program_id,
            payload: ['status' => 'planned', 'competency_id' => $competencyId]
        );

        return [
            'status' => 'accepted',
            'decision' => $decisionRecord,
            'intervention' => $intervention,
        ];
    }
}
