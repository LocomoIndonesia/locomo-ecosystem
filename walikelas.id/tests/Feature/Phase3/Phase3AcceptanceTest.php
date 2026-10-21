<?php

namespace Tests\Feature\Phase3;

use App\Models\AiRun;
use App\Models\Assignment;
use App\Models\Competency;
use App\Models\Consent;
use App\Models\Enrollment;
use App\Models\GuardianRelation;
use App\Models\Learner;
use App\Models\LearningPlan;
use App\Models\LearningProgram;
use App\Models\MasteryState;
use App\Models\Mission;
use App\Models\PlanVersion;
use App\Models\ProgramEducator;
use App\Models\Report;
use App\Models\Response as StudentResponse;
use App\Models\Rubric;
use App\Models\User;
use App\Policies\ProgramPolicy;
use App\Services\GeminiService;
use App\Services\InterventionService;
use App\Services\OutboxNotificationService;
use App\Services\PlanningCopilotService;
use App\Services\ProgressReportService;
use App\Services\RubricScorerService;
use App\Services\TriageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class Phase3AcceptanceTest extends TestCase
{
    use DatabaseTransactions;

    protected User $educator;
    protected LearningProgram $program;
    protected Enrollment $enrollment;
    protected Learner $learner;
    protected Competency $competency;
    protected Rubric $rubric;
    protected PlanVersion $planVersion;

    protected TriageService $triageService;
    protected RubricScorerService $rubricScorerService;
    protected InterventionService $interventionService;
    protected ProgressReportService $reportService;
    protected OutboxNotificationService $outboxService;
    protected PlanningCopilotService $planningService;
    protected ProgramPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->triageService = app(TriageService::class);
        $this->rubricScorerService = app(RubricScorerService::class);
        $this->interventionService = app(InterventionService::class);
        $this->reportService = app(ProgressReportService::class);
        $this->outboxService = app(OutboxNotificationService::class);
        $this->planningService = app(PlanningCopilotService::class);
        $this->policy = app(ProgramPolicy::class);

        // Setup Educator & Active Program
        $this->educator = User::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Guru Fase 3',
            'email' => 'guru.fase3_' . Str::random(8) . '@walikelas.id',
        ]);

        $this->program = LearningProgram::create([
            'id' => Str::uuid()->toString(),
            'mode' => 'school',
            'title' => 'Program Fase 3 Literasi',
            'grade_level' => 3,
            'status' => 'active',
            'created_by' => $this->educator->id,
        ]);

        ProgramEducator::create([
            'id' => Str::uuid()->toString(),
            'program_id' => $this->program->id,
            'user_id' => $this->educator->id,
            'role' => 'lead',
            'started_at' => now(),
        ]);

        $this->learner = Learner::create([
            'id' => Str::uuid()->toString(),
            'display_name' => 'Siswa Fase 3',
            'created_by' => $this->educator->id,
        ]);

        $this->enrollment = Enrollment::create([
            'id' => Str::uuid()->toString(),
            'program_id' => $this->program->id,
            'learner_id' => $this->learner->id,
            'status' => 'active',
        ]);

        $this->competency = Competency::where('code', 'LIT-B-EXP-01')->first() ?: Competency::create([
            'id' => Str::uuid()->toString(),
            'code' => 'LIT-B-EXP-01',
            'phase' => 'B',
            'title' => 'Menemukan informasi tersurat',
            'domain' => 'literasi_membaca',
            'description' => 'Menemukan informasi tersurat dalam teks fabel.',
            'version' => '1.0',
            'status' => 'published',
            'prerequisites' => [],
        ]);

        $this->rubric = Rubric::where('competency_id', $this->competency->id)->first() ?: Rubric::create([
            'id' => Str::uuid()->toString(),
            'competency_id' => $this->competency->id,
            'title' => 'Rubrik Identifikasi Karakter Fabel',
            'criteria' => [
                ['level' => 4, 'label' => 'Sangat Mahir', 'desc' => 'Menyebutkan karakter dan detail ciri fisik.'],
                ['level' => 2, 'label' => 'Perlu Pendampingan', 'desc' => 'Belum mampu menyebutkan karakter.'],
            ],
            'version' => '1.0',
            'status' => 'active',
        ]);

        $plan = LearningPlan::create([
            'id' => Str::uuid()->toString(),
            'program_id' => $this->program->id,
        ]);

        $this->planVersion = PlanVersion::create([
            'id' => Str::uuid()->toString(),
            'plan_id' => $plan->id,
            'version_no' => 1,
            'status' => 'approved',
            'source_type' => 'curated_wizard',
            'content' => ['title' => 'Rencana Fase 3', 'grade_level' => 3],
            'created_by' => $this->educator->id,
            'approved_by' => $this->educator->id,
            'approved_at' => now(),
        ]);

        $this->program->update(['active_plan_version_id' => $this->planVersion->id]);
    }

    /**
     * TAT-10: Analisis kesenjangan tanpa bukti asesmen wajib mengembalikan uncertainty: insufficient_evidence
     */
    public function test_tat_10_gap_analysis_without_evidence_returns_insufficient_evidence(): void
    {
        // Siswa baru tanpa penugasan atau respons apa pun
        $analysis = $this->interventionService->analyzeGaps($this->enrollment);

        $this->assertEquals('insufficient_evidence', $analysis['uncertainty']);
        $this->assertStringContainsString('Belum ada bukti data respons', $analysis['rationale']);
        $this->assertEmpty($analysis['gaps']);
    }

    /**
     * TAT-11: Pendidik menolak usulan intervensi AI -> Rekomendasi diabaikan; tidak ada intervensi yang dibuat (TI-05)
     */
    public function test_tat_11_educator_rejects_ai_intervention_creates_no_intervention(): void
    {
        // 1. Copilot mengusulkan 1 intervensi
        $suggestion = $this->interventionService->proposeIntervention(
            enrollment: $this->enrollment,
            competency: $this->competency,
            description: 'Membaca terpandu tambahan 15 menit',
            rationale: 'Siswa memerlukan pengulangan identifikasi detail tersurat.',
            uncertainty: 'medium'
        );

        $this->assertEquals('pending', $suggestion->status);

        // 2. Pendidik menolak usulan intervensi tersebut
        $result = $this->interventionService->handleEducatorDecision(
            educator: $this->educator,
            suggestion: $suggestion,
            decision: 'reject',
            reason: 'Siswa sudah menunjukkan kemajuan di sesi kelas mandiri, tidak perlu remedial.'
        );

        // 3. Verifikasi kriteria TI-05: status suggestion menjadi rejected & TIDAK ADA record intervensi
        $this->assertEquals('rejected', $result['status']);
        $this->assertNull($result['intervention']);
        $this->assertEquals('rejected', $suggestion->fresh()->status);
        $this->assertDatabaseMissing('interventions', [
            'enrollment_id' => $this->enrollment->id,
            'competency_id' => $this->competency->id,
        ]);
        $this->assertDatabaseHas('educator_decisions', [
            'suggestion_id' => $suggestion->id,
            'decision' => 'reject',
            'decided_by' => $this->educator->id,
        ]);
    }

    /**
     * TAT-12: Koreksi nilai asesmen setelah laporan disahkan -> Laporan lama utuh; status ditandai needs_review (TI-12)
     */
    public function test_tat_12_evidence_correction_invalidates_approved_report_to_needs_review(): void
    {
        // 1. Buat respons asesmen dan validasi mastery awal oleh guru
        $mission = Mission::first() ?: Mission::create([
            'id' => Str::uuid()->toString(),
            'code' => 'MIS-TEST-01',
            'title' => 'Misi Uji',
            'kind' => 'digital',
            'grade_level' => 3,
            'competency_ids' => [$this->competency->id],
            'steps' => [],
            'status' => 'published',
        ]);

        $assignment = Assignment::create([
            'id' => Str::uuid()->toString(),
            'program_id' => $this->program->id,
            'enrollment_id' => $this->enrollment->id,
            'mission_id' => $mission->id,
            'plan_version_id' => $this->planVersion->id,
            'assigned_by' => $this->educator->id,
            'status' => 'submitted',
        ]);

        $attempt = \App\Models\Attempt::create([
            'id' => Str::uuid()->toString(),
            'assignment_id' => $assignment->id,
            'started_at' => now(),
            'finished_at' => now(),
            'active_seconds' => 120,
        ]);

        $response = StudentResponse::create([
            'id' => Str::uuid()->toString(),
            'attempt_id' => $attempt->id,
            'step_key' => 'step_1',
            'answer' => ['text' => 'Jejak berang-berang di tepi kolam.'],
            'created_at' => now(),
        ]);

        $mastery = $this->rubricScorerService->validateMasteryByEducator(
            enrollment: $this->enrollment,
            competency: $this->competency,
            level: 'indicated_mastery',
            educator: $this->educator,
            basisRefs: [$response->id]
        );

        // 2. Terbitkan laporan 3-lapis berstatus approved
        $report = $this->reportService->createReport(
            program: $this->program,
            enrollment: $this->enrollment,
            educator: $this->educator,
            autoApprove: true
        );

        $this->assertEquals('approved', $report->status);
        $this->assertDatabaseHas('report_sources', [
            'report_id' => $report->id,
            'source_type' => 'responses',
            'source_id' => $response->id,
        ]);

        // 3. Guru kemudian mengoreksi nilai/mastery karena menemukan bukti baru
        $this->rubricScorerService->validateMasteryByEducator(
            enrollment: $this->enrollment,
            competency: $this->competency,
            level: 'needs_support',
            educator: $this->educator,
            basisRefs: [$response->id]
        );

        // 4. Verifikasi TI-12: Laporan lama tetap utuh, tetapi statusnya menjadi 'needs_review'
        $refreshedReport = $report->fresh();
        $this->assertEquals('needs_review', $refreshedReport->status);
        $this->assertNotNull($refreshedReport->facts_layer);
        $this->assertNotNull($refreshedReport->findings_layer);
        $this->assertEquals(1, $refreshedReport->version_no);
    }

    /**
     * TAT-14: Notifikasi saat adapter WA offline -> Pesan tersimpan sebagai tautan web aman; status tercatat jujur (TI-09)
     */
    public function test_tat_14_whatsapp_offline_falls_back_to_web_link_with_honest_status(): void
    {
        $guardianUser = User::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Wali WhatsApp',
            'email' => 'wali.wa_' . Str::random(8) . '@example.com',
        ]);

        $relation = GuardianRelation::create([
            'id' => Str::uuid()->toString(),
            'user_id' => $guardianUser->id,
            'learner_id' => $this->learner->id,
            'relation_type' => 'parent',
            'status' => 'verified',
        ]);

        Consent::create([
            'id' => Str::uuid()->toString(),
            'guardian_relation_id' => $relation->id,
            'program_id' => $this->program->id,
            'purpose_scope' => 'PROGRAM_PARTICIPATION',
            'policy_version' => 'v1.0',
            'granted_at' => now(),
        ]);

        // Kirim notifikasi dengan adapter WhatsApp disimulasikan offline
        $outbox = $this->outboxService->queueNotification(
            guardianRelation: $relation,
            program: $this->program,
            templateKey: 'PROGRESS_UPDATE',
            channel: 'whatsapp',
            payload: ['simulate_wa_offline' => true]
        );

        // TI-09: Status tercatat jujur, fallback ke tautan web aman
        $this->assertEquals('web_link', $outbox->channel);
        $this->assertEquals('link_ready', $outbox->status);
        $this->assertEquals('OFFLINE', $outbox->policy_check['adapter_status']);
        $this->assertEquals('web_link', $outbox->policy_check['fallback_channel']);
    }

    /**
     * TAT-15: Pendidik menutup program (completed) -> Data historis dan bukti capaian tetap dapat ditinjau (read-only)
     */
    public function test_tat_15_educator_completing_program_preserves_historical_read_only_access(): void
    {
        // 1. Tutup program
        $this->program->complete($this->educator);
        $this->assertEquals('completed', $this->program->fresh()->status);

        // 2. Otorisasi policy masih mengizinkan peninjauan riwayat bukti (TI-02)
        $this->assertTrue($this->policy->manage($this->educator, $this->program));
        $this->assertTrue($this->policy->view($this->educator, $this->program));

        // 3. Namun aksi mutasi rencana dilarang pada program yang sudah ditutup
        $this->assertFalse($this->policy->canMutate($this->educator, $this->program));

        // 4. Data historis enrollments dan reports tetap ada dan terbaca
        $this->assertCount(1, $this->program->enrollments);
    }

    /**
     * TAT-18: Ekstraksi rencana kurikulum ambigu -> AI menandai field ambigu; melarang klaim pasti sesuai regulasi
     */
    public function test_tat_18_plan_extraction_with_ambiguity_flags_ambiguities_without_false_guarantees(): void
    {
        $rawText = "Silabus Membaca Bahasa Indonesia: Siswa diajak membaca fabel sederhana dan berdiskusi bersama.";

        $planVersion = $this->planningService->importFromText($this->program, $rawText, $this->educator);

        $this->assertNotEmpty($planVersion->ambiguities);
        $this->assertContains(
            'Alokasi waktu pertemuan tidak disebutkan secara spesifik dalam silabus.',
            $planVersion->ambiguities
        );
        $this->assertContains(
            'Kriteria keberhasilan/rubrik belum memiliki pedoman penskoran eksplisit.',
            $planVersion->ambiguities
        );
    }

    /**
     * TAT-21: Gemini API timeout atau respons JSON rusak -> Sistem mencatat error; UI menampilkan opsi input manual (TI-14)
     */
    public function test_tat_21_gemini_timeout_logs_error_and_returns_manual_scoring_fallback(): void
    {
        $mission = Mission::first() ?: Mission::create([
            'id' => Str::uuid()->toString(),
            'code' => 'MIS-TEST-02',
            'title' => 'Misi Uji 2',
            'kind' => 'digital',
            'grade_level' => 3,
            'competency_ids' => [$this->competency->id],
            'steps' => [],
            'status' => 'published',
        ]);

        $assignment = Assignment::create([
            'id' => Str::uuid()->toString(),
            'program_id' => $this->program->id,
            'enrollment_id' => $this->enrollment->id,
            'mission_id' => $mission->id,
            'plan_version_id' => $this->planVersion->id,
            'assigned_by' => $this->educator->id,
            'status' => 'submitted',
        ]);

        $attempt = \App\Models\Attempt::create([
            'id' => Str::uuid()->toString(),
            'assignment_id' => $assignment->id,
            'started_at' => now(),
            'finished_at' => now(),
            'active_seconds' => 120,
        ]);

        $response = StudentResponse::create([
            'id' => Str::uuid()->toString(),
            'attempt_id' => $attempt->id,
            'step_key' => 'step_open_prompt',
            'answer' => ['text' => 'Tamu kolam memiliki kaki berselaput.'],
            'created_at' => now(),
        ]);

        // Evaluasi dengan menyimulasikan failure/timeout pada Gemini
        $result = $this->rubricScorerService->evaluateResponse(
            response: $response,
            rubric: $this->rubric,
            programId: $this->program->id,
            simulateFailure: true
        );

        // TI-14: Sistem tidak crash, mencatat timeout ke ai_runs, dan menyediakan opsi manual review
        $this->assertEquals('manual_review_required', $result['status']);
        $this->assertTrue($result['requires_manual_scoring']);
        $this->assertNotNull($result['ai_run_id']);

        $this->assertDatabaseHas('ai_runs', [
            'id' => $result['ai_run_id'],
            'purpose' => 'rubric_score',
            'status' => 'timeout',
        ]);
    }
}
