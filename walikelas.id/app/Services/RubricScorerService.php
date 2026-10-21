<?php

namespace App\Services;

use App\Models\AiRun;
use App\Models\AuditEvent;
use App\Models\Competency;
use App\Models\Enrollment;
use App\Models\MasteryState;
use App\Models\Response as StudentResponse;
use App\Models\Rubric;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;

class RubricScorerService
{
    public function __construct(
        protected GeminiService $geminiService,
        protected ProgressReportService $reportService
    ) {}

    /**
     * Mengevaluasi respons siswa menggunakan Gemini dengan skema ketat & penanganan error (TI-05, TI-14, TAT-21)
     */
    public function evaluateResponse(
        StudentResponse $response,
        Rubric $rubric,
        ?string $programId = null,
        bool $simulateFailure = false
    ): array {
        if ($simulateFailure) {
            $aiRunId = Str::uuid()->toString();
            AiRun::create([
                'id' => $aiRunId,
                'purpose' => 'rubric_score',
                'program_id' => $programId,
                'prompt_version' => 'v1.0',
                'model_name' => 'gemini-1.5-flash',
                'input_refs' => ['response_id' => $response->id, 'rubric_id' => $rubric->id],
                'output_payload' => ['error' => 'API connection timeout after 25000ms'],
                'status' => 'timeout',
                'latency_ms' => 25000,
                'created_at' => now(),
            ]);

            return [
                'status' => 'manual_review_required',
                'error' => 'Gemini API timeout or invalid payload',
                'requires_manual_scoring' => true,
                'ai_run_id' => $aiRunId,
            ];
        }

        $systemPrompt = "Anda adalah asisten penilai guru SD yang objektif dan berempati. Evaluasi jawaban siswa terhadap kriteria rubrik dengan tepat.";
        $answerText = is_array($response->answer) ? json_encode($response->answer) : (string) ($response->answer ?? '');
        $userPrompt = "Rubrik: {$rubric->title}\nKriteria: " . json_encode($rubric->criteria) . "\nJawaban Siswa: {$answerText}";

        $schema = [
            'type' => 'object',
            'properties' => [
                'score' => ['type' => 'integer'],
                'level' => ['type' => 'string'],
                'criteria_met' => ['type' => 'array', 'items' => ['type' => 'string']],
                'rationale' => ['type' => 'string'],
                'feedback_for_student' => ['type' => 'string'],
                'uncertainty' => ['type' => 'string'],
            ],
            'required' => ['score', 'level', 'criteria_met', 'rationale', 'feedback_for_student', 'uncertainty'],
        ];

        $result = $this->geminiService->generateStructured(
            purpose: 'rubric_score',
            systemPrompt: $systemPrompt,
            userPrompt: $userPrompt,
            jsonSchema: $schema,
            programId: $programId,
            inputRefs: ['response_id' => $response->id, 'rubric_id' => $rubric->id]
        );

        return $result;
    }

    /**
     * Pengesahan nilai oleh Pendidik (Human-in-the-Loop, TI-05, TI-06, TAT-12, TAT-19)
     */
    public function validateMasteryByEducator(
        Enrollment $enrollment,
        Competency $competency,
        string $level,
        User $educator,
        array $basisRefs = []
    ): MasteryState {
        // TI-06: Hanya pendidik yang dapat menetapkan level 'educator_validated'
        $mastery = MasteryState::updateOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'competency_id' => $competency->id,
            ],
            [
                'level' => $level,
                'basis_refs' => $basisRefs,
                'decided_by_type' => 'educator',
                'decided_by' => $educator->id,
                'created_at' => now(),
            ]
        );

        AuditEvent::log(
            actorType: 'educator',
            actorId: $educator->id,
            action: 'EDUCATOR_VALIDATED_MASTERY',
            subjectType: 'mastery_state',
            subjectId: $mastery->id,
            programId: $enrollment->program_id,
            payload: [
                'competency_code' => $competency->code,
                'level' => $level,
                'basis_refs' => $basisRefs,
            ]
        );

        // TI-12 & TAT-12: Jika ada laporan yang telah disahkan menggunakan bukti/mastery ini, tandai needs_review
        $this->reportService->invalidateOnEvidenceChange('mastery_states', $mastery->id);
        foreach ($basisRefs as $refId) {
            $this->reportService->invalidateOnEvidenceChange('responses', (string) $refId);
        }

        return $mastery;
    }
}
