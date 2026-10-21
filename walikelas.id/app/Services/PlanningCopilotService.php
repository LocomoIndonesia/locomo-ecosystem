<?php

namespace App\Services;

use App\Models\AuditEvent;
use App\Models\LearningPlan;
use App\Models\LearningProgram;
use App\Models\PlanSource;
use App\Models\PlanVersion;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;

class PlanningCopilotService
{
    public function __construct(
        protected GeminiService $gemini
    ) {}

    /**
     * Impor teks silabus/RPP bebas menjadi draf rencana ajar berversi (TI-04, TAT-03, TAT-18)
     */
    public function importFromText(LearningProgram $program, string $rawText, User $educator): PlanVersion
    {
        $plan = LearningPlan::firstOrCreate(
            ['program_id' => $program->id],
            ['id' => Str::uuid()->toString(), 'created_at' => now()]
        );

        $systemPrompt = "Anda adalah Copilot Perencanaan Kurikulum WaliKelas.id untuk Fase B (Kelas 3 SD). "
            . "Tugas Anda mengekstraksi silabus bebas menjadi sasaran ajar, alur aktivitas, dan strategi asesmen. "
            . "Jika ada hal yang tidak lengkap (seperti durasi per sesi atau rubrik penskoran), tandai sebagai ambiguitas.";

        $jsonSchema = [
            'type' => 'object',
            'required' => ['title', 'grade_level', 'objectives', 'activities', 'assessment_strategy', 'ambiguities'],
            'properties' => [
                'title' => ['type' => 'string'],
                'grade_level' => ['type' => 'integer'],
                'objectives' => ['type' => 'array', 'items' => ['type' => 'string']],
                'activities' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'required' => ['session', 'name', 'duration_minutes', 'description'],
                        'properties' => [
                            'session' => ['type' => 'integer'],
                            'name' => ['type' => 'string'],
                            'duration_minutes' => ['type' => 'integer'],
                            'description' => ['type' => 'string'],
                        ],
                    ],
                ],
                'assessment_strategy' => [
                    'type' => 'object',
                    'required' => ['type', 'frequency', 'rubrics'],
                    'properties' => [
                        'type' => ['type' => 'string'],
                        'frequency' => ['type' => 'string'],
                        'rubrics' => ['type' => 'array', 'items' => ['type' => 'string']],
                    ],
                ],
                'ambiguities' => ['type' => 'array', 'items' => ['type' => 'string']],
            ],
        ];

        $extracted = $this->gemini->generateStructured(
            purpose: 'plan_extract',
            systemPrompt: $systemPrompt,
            userPrompt: $rawText,
            jsonSchema: $jsonSchema,
            programId: $program->id,
            inputRefs: ['text_length' => strlen($rawText)]
        );

        $latestVersionNo = (int) $plan->versions()->max('version_no') ?: 0;
        $nextVersionNo = $latestVersionNo + 1;

        // TI-04: Seluruh ekstraksi AI WAJIB berstatus 'draft', dilarang auto-approve!
        $planVersion = PlanVersion::create([
            'id' => Str::uuid()->toString(),
            'plan_id' => $plan->id,
            'version_no' => $nextVersionNo,
            'status' => 'draft',
            'source_type' => 'import',
            'content' => [
                'title' => $extracted['title'] ?? 'Rencana Hasil Impor',
                'grade_level' => $extracted['grade_level'] ?? 3,
                'objectives' => $extracted['objectives'] ?? [],
                'activities' => $extracted['activities'] ?? [],
                'assessment_strategy' => $extracted['assessment_strategy'] ?? [],
            ],
            'ambiguities' => $extracted['ambiguities'] ?? [],
            'created_by' => $educator->id,
            'created_at' => now(),
        ]);

        PlanSource::create([
            'id' => Str::uuid()->toString(),
            'plan_version_id' => $planVersion->id,
            'kind' => 'pasted_text',
            'raw_text' => $rawText,
            'extracted' => $extracted,
            'created_at' => now(),
        ]);

        AuditEvent::log(
            actorType: 'user',
            actorId: $educator->id,
            action: 'PLAN_IMPORTED',
            subjectType: 'plan_version',
            subjectId: $planVersion->id,
            programId: $program->id,
            payload: ['version_no' => $nextVersionNo, 'status' => 'draft']
        );

        return $planVersion;
    }

    /**
     * Menyusun draf rencana ajar melalui Wizard 5 langkah berbasis kompetensi kurasi (TAT-04)
     */
    public function generateFromWizard(LearningProgram $program, array $wizardData, User $educator): PlanVersion
    {
        $plan = LearningPlan::firstOrCreate(
            ['program_id' => $program->id],
            ['id' => Str::uuid()->toString(), 'created_at' => now()]
        );

        $latestVersionNo = (int) $plan->versions()->max('version_no') ?: 0;
        $nextVersionNo = $latestVersionNo + 1;

        $planVersion = PlanVersion::create([
            'id' => Str::uuid()->toString(),
            'plan_id' => $plan->id,
            'version_no' => $nextVersionNo,
            'status' => 'draft',
            'source_type' => 'wizard',
            'content' => [
                'title' => $wizardData['title'] ?? 'Rencana Kurasi Wizard Literasi',
                'grade_level' => $wizardData['grade_level'] ?? 3,
                'competency_codes' => $wizardData['competency_codes'] ?? ['LIT-B-EXP-01'],
                'objectives' => $wizardData['objectives'] ?? [
                    'Mengidentifikasi informasi tersurat fabel (LIT-B-EXP-01)',
                ],
                'session_count' => $wizardData['session_count'] ?? 4,
                'activities' => $wizardData['activities'] ?? [],
                'assessment_strategy' => [
                    'type' => 'formative_checkpoint',
                    'frequency' => 'weekly',
                    'rubrics' => $wizardData['competency_codes'] ?? ['LIT-B-EXP-01'],
                ],
            ],
            'ambiguities' => [],
            'created_by' => $educator->id,
            'created_at' => now(),
        ]);

        AuditEvent::log(
            actorType: 'user',
            actorId: $educator->id,
            action: 'PLAN_WIZARD_CREATED',
            subjectType: 'plan_version',
            subjectId: $planVersion->id,
            programId: $program->id,
            payload: ['version_no' => $nextVersionNo, 'status' => 'draft']
        );

        return $planVersion;
    }

    /**
     * Pengesahan mutlak versi rencana ajar oleh pendidik pengampu (TI-04, TAT-05)
     */
    public function approvePlanVersion(LearningProgram $program, PlanVersion $version, User $educator): PlanVersion
    {
        if ($version->plan->program_id !== $program->id) {
            throw new Exception('Plan version does not belong to this program.');
        }

        // Tandai versi lama yang pernah disahkan menjadi superseded
        $program->learningPlan->versions()
            ->where('status', 'approved')
            ->where('id', '<>', $version->id)
            ->update(['status' => 'superseded']);

        // Sahkan versi target
        $version->update([
            'status' => 'approved',
            'approved_by' => $educator->id,
            'approved_at' => now(),
        ]);

        // Aktifkan pada program
        $program->update([
            'active_plan_version_id' => $version->id,
            'status' => 'active',
        ]);

        AuditEvent::log(
            actorType: 'user',
            actorId: $educator->id,
            action: 'PLAN_VERSION_APPROVED',
            subjectType: 'plan_version',
            subjectId: $version->id,
            programId: $program->id,
            payload: ['version_no' => $version->version_no, 'approved_by' => $educator->id]
        );

        return $version->fresh();
    }

    /**
     * Revisi rencana aktif: Menghasilkan versi baru (N+1) tanpa merusak keterikatan tugas lama (TI-04, TAT-23)
     */
    public function revisePlanVersion(LearningProgram $program, PlanVersion $currentVersion, array $editedContent, User $educator): PlanVersion
    {
        $plan = $program->learningPlan;
        $nextVersionNo = ((int) $plan->versions()->max('version_no')) + 1;

        $newVersion = PlanVersion::create([
            'id' => Str::uuid()->toString(),
            'plan_id' => $plan->id,
            'version_no' => $nextVersionNo,
            'status' => 'draft', // Draf baru menunggu penelaahan ulang pendidik
            'source_type' => 'revision',
            'content' => $editedContent,
            'ambiguities' => [],
            'created_by' => $educator->id,
            'created_at' => now(),
        ]);

        AuditEvent::log(
            actorType: 'user',
            actorId: $educator->id,
            action: 'PLAN_VERSION_REVISED',
            subjectType: 'plan_version',
            subjectId: $newVersion->id,
            programId: $program->id,
            payload: [
                'from_version' => $currentVersion->version_no,
                'new_version' => $nextVersionNo,
                'status' => 'draft',
            ]
        );

        return $newVersion;
    }
}
