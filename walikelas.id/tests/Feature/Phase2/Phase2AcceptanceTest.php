<?php

namespace Tests\Feature\Phase2;

use App\Models\Assignment;
use App\Models\Competency;
use App\Models\Enrollment;
use App\Models\GuardianRelation;
use App\Models\Learner;
use App\Models\LearningProgram;
use App\Models\MasteryState;
use App\Models\Mission;
use App\Models\PlanVersion;
use App\Models\ProgramEducator;
use App\Models\User;
use App\Policies\ProgramPolicy;
use App\Services\AssignmentService;
use App\Services\AssignmentStateMachine;
use App\Services\LaunchTokenService;
use App\Services\LearnerEnrollmentService;
use App\Services\OutboxNotificationService;
use App\Services\PlanningCopilotService;
use App\Services\PrerequisiteAdaptiveService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class Phase2AcceptanceTest extends TestCase
{
    use DatabaseTransactions;

    protected User $educator;
    protected LearningProgram $program;
    protected Enrollment $enrollment;
    protected Learner $learner;
    protected AssignmentService $assignmentService;
    protected AssignmentStateMachine $fsm;
    protected LaunchTokenService $tokenService;
    protected OutboxNotificationService $outboxService;
    protected PrerequisiteAdaptiveService $adaptiveService;
    protected LearnerEnrollmentService $enrollmentService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assignmentService = app(AssignmentService::class);
        $this->fsm = app(AssignmentStateMachine::class);
        $this->tokenService = app(LaunchTokenService::class);
        $this->outboxService = app(OutboxNotificationService::class);
        $this->adaptiveService = app(PrerequisiteAdaptiveService::class);
        $this->enrollmentService = app(LearnerEnrollmentService::class);

        // Setup Educator & Active Program dengan Plan Ter-approve
        $this->educator = User::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Guru Fase 2',
            'email' => 'guru.fase2_' . Str::random(8) . '@walikelas.id',
            'password_hash' => bcrypt('secret123'),
        ]);

        $this->program = LearningProgram::create([
            'id' => Str::uuid()->toString(),
            'mode' => 'school',
            'title' => 'Program Literasi Fase 2',
            'grade_level' => 3,
            'status' => 'draft',
            'created_by' => $this->educator->id,
        ]);

        ProgramEducator::create([
            'id' => Str::uuid()->toString(),
            'program_id' => $this->program->id,
            'user_id' => $this->educator->id,
            'role' => 'lead',
            'started_at' => now(),
        ]);

        // Buat dan sahkan rencana ajar agar penugasan diizinkan (TI-11)
        $copilot = app(PlanningCopilotService::class);
        $planVersion = $copilot->importFromText($this->program, 'Silabus Kurikulum Fase 2', $this->educator);
        $copilot->approvePlanVersion($this->program, $planVersion, $this->educator);

        // Daftarkan siswa
        $this->enrollment = $this->enrollmentService->enrollLearner(
            $this->program,
            'Rian Penguji',
            3,
            'avatar_kancil_01',
            $this->educator
        );
        $this->learner = $this->enrollment->learner;
    }

    /**
     * TAT-06: Siswa gagal prasyarat pada asesmen awal.
     * Sistem merekomendasikan misi dasar prasyarat, tidak memaksakan teks sulit.
     */
    public function test_tat_06_unmet_prerequisite_recommends_foundational_mission(): void
    {
        // LIT-B-SEQ-02 memiliki prasyarat LIT-B-EXP-01
        $compSeq = Competency::where('code', 'LIT-B-SEQ-02')->first();
        $this->assertNotNull($compSeq, 'Competency LIT-B-SEQ-02 must exist.');

        // Siswa belum memiliki rekaman mastery pada LIT-B-EXP-01
        $recommendation = $this->adaptiveService->getRecommendedMission($this->enrollment, $compSeq);

        $this->assertFalse($recommendation['can_proceed_target']);
        $this->assertEquals('PREREQUISITE_NOT_MET', $recommendation['reason']);
        $this->assertEquals('LIT-B-EXP-01', $recommendation['recommended_competency']->code);
        $this->assertNotNull($recommendation['recommended_mission']);
    }

    /**
     * TAT-08: Kirim laporan ke wali yang belum diverifikasi dua sisi (TI-08).
     * Outbox mencatat 'blocked_policy', tidak ada pesan keluar.
     */
    public function test_tat_08_unverified_guardian_notification_blocked_by_policy(): void
    {
        // Buat relasi wali yang masih berstatus 'invited' atau 'claimed' (belum verified)
        $unverifiedRelation = GuardianRelation::create([
            'id' => Str::uuid()->toString(),
            'learner_id' => $this->learner->id,
            'status' => 'claimed', // Belum diverifikasi pendidik
            'created_at' => now(),
        ]);

        $outbox = $this->outboxService->queueNotification(
            $unverifiedRelation,
            $this->program,
            'PROGRESS_REPORT_WEEKLY_V1'
        );

        $this->assertEquals('blocked_policy', $outbox->status);
        $this->assertEquals('REJECTED_UNVERIFIED_GUARDIAN', $outbox->policy_check['policy_result']);

        $this->assertDatabaseHas('outbox_messages', [
            'id' => $outbox->id,
            'status' => 'blocked_policy',
        ]);
    }

    /**
     * TAT-09: Wali konfirmasi 'done' pada tugas rumah (TI-07).
     * Status tugas bertransisi ke 'submitted'; mastery_states DILARANG BERUBAH.
     */
    public function test_tat_09_guardian_done_confirmation_submits_task_without_altering_mastery(): void
    {
        $homeMission = Mission::where('kind', 'home')->first();
        $this->assertNotNull($homeMission, 'Home mission must exist.');

        $assignment = $this->assignmentService->assignMission(
            $this->program->fresh(),
            $this->enrollment,
            $homeMission,
            $this->educator,
            ['requires_guardian_confirmation' => true]
        );

        // Alihkan tugas ke status 'needs_guardian'
        $this->fsm->transition($assignment, 'needs_guardian', 'system', null, 'Menunggu konfirmasi');
        $this->assertEquals('needs_guardian', $assignment->fresh()->status);

        // Buat wali terverifikasi
        $guardianUser = User::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Ibu Rian Terverifikasi',
            'email' => 'ibu.rian_' . Str::random(8) . '@example.com',
            'password_hash' => bcrypt('secret123'),
        ]);

        $guardianRelation = GuardianRelation::create([
            'id' => Str::uuid()->toString(),
            'learner_id' => $this->learner->id,
            'guardian_user_id' => $guardianUser->id,
            'status' => 'verified',
            'created_at' => now(),
        ]);

        $initialMasteryCount = MasteryState::where('enrollment_id', $this->enrollment->id)->count();

        // Wali mengonfirmasi 'done'
        $confirmation = $this->fsm->recordGuardianConfirmation(
            $assignment,
            $guardianRelation,
            'done',
            'Ananda bercerita dengan lancar dan gembira.'
        );

        // 1. Status penugasan bertransisi ke 'submitted'
        $this->assertEquals('submitted', $assignment->fresh()->status);

        // 2. Invarian TI-07: Konfirmasi orang tua DILARANG mengubah mastery_states siswa
        $finalMasteryCount = MasteryState::where('enrollment_id', $this->enrollment->id)->count();
        $this->assertEquals($initialMasteryCount, $finalMasteryCount);
    }

    /**
     * TAT-13: Siswa menyelesaikan langkah akhir misi membaca digital.
     * Status bertransisi tuntas dan dapat diarahkan ke jeda layar tanpa infinite loop.
     */
    public function test_tat_13_learner_completes_final_digital_step_smoothly(): void
    {
        $digitalMission = Mission::where('kind', 'digital')->first();
        $assignment = $this->assignmentService->assignMission(
            $this->program->fresh(),
            $this->enrollment,
            $digitalMission,
            $this->educator,
            ['requires_guardian_confirmation' => false]
        );

        $attempt = $this->fsm->startMission($assignment);
        $this->assertEquals('in_progress', $assignment->fresh()->status);

        // Selesaikan digital steps
        $this->fsm->completeDigitalSteps($assignment, $attempt, 300);

        // Tugas bertransisi ke submitted
        $this->assertEquals('submitted', $assignment->fresh()->status);
        $this->assertNotNull($attempt->fresh()->finished_at);
        $this->assertEquals(300, $attempt->fresh()->active_seconds);
    }

    /**
     * TAT-16: Siswa mengetik kata kunci bahaya pada respons membaca (TI-10).
     * safety_flag = true, sesi dijeda, audit event darurat tercatat.
     */
    public function test_tat_16_safeguarding_circuit_breaker_triggers_on_danger_keywords(): void
    {
        $digitalMission = Mission::where('kind', 'digital')->first();
        $assignment = $this->assignmentService->assignMission(
            $this->program->fresh(),
            $this->enrollment,
            $digitalMission,
            $this->educator
        );

        $attempt = $this->fsm->startMission($assignment);

        // Siswa memasukkan teks yang mengandung kata kunci bahaya
        $dangerText = "Saya merasa takut sekali karena kemarin melihat ada darah dan dipukul";
        $response = $this->fsm->recordStepResponse(
            $attempt,
            'step_essay_reflection',
            ['answer_text' => $dangerText]
        );

        // TI-10 & TAT-16: safety_flag harus diset true
        $this->assertTrue($response->safety_flag);

        $this->assertDatabaseHas('responses', [
            'id' => $response->id,
            'safety_flag' => true,
        ]);

        $this->assertDatabaseHas('audit_events', [
            'action' => 'EMERGENCY_SAFETY_CIRCUIT_BREAKER_TRIGGERED',
            'subject_id' => $response->id,
        ]);
    }

    /**
     * TAT-22: Akses misi menggunakan launch token kedaluwarsa atau tidak valid.
     * Ditolak HTTP 404; token tidak dapat dipakai ulang.
     */
    public function test_tat_22_expired_or_revoked_launch_token_rejected_with_404(): void
    {
        $mission = Mission::first();
        $assignment = $this->assignmentService->assignMission(
            $this->program->fresh(),
            $this->enrollment,
            $mission,
            $this->educator
        );

        // 1. Uji token tidak dikenal -> HARUS DITOLAK 404
        $fakeToken = Str::random(40);
        $rejectedNonExistent = false;
        try {
            $this->tokenService->validateToken($fakeToken);
        } catch (NotFoundHttpException $e) {
            $rejectedNonExistent = true;
        }
        $this->assertTrue($rejectedNonExistent, 'Non-existent token must throw 404.');

        // 2. Uji token kedaluwarsa -> HARUS DITOLAK 404
        $tokenData = $this->tokenService->generateLaunchToken($assignment, validHours: 1);
        $tokenData['launch_token']->update(['expires_at' => now()->subDay()]);

        $rejectedExpired = false;
        try {
            $this->tokenService->validateToken($tokenData['raw_token']);
        } catch (NotFoundHttpException $e) {
            $rejectedExpired = true;
        }
        $this->assertTrue($rejectedExpired, 'Expired token must throw 404.');

        // 3. Uji token dicabut -> HARUS DITOLAK 404
        $validTokenData = $this->tokenService->generateLaunchToken($assignment, validHours: 24);
        $this->tokenService->revokeToken($validTokenData['launch_token']);

        $rejectedRevoked = false;
        try {
            $this->tokenService->validateToken($validTokenData['raw_token']);
        } catch (NotFoundHttpException $e) {
            $rejectedRevoked = true;
        }
        $this->assertTrue($rejectedRevoked, 'Revoked token must throw 404.');
    }

    /**
     * TAT-24: Wali mencabut persetujuan (consent) partisipasi.
     * Akses portal wali untuk program tersebut seketika nonaktif.
     */
    public function test_tat_24_revoking_guardian_consent_immediately_disables_access(): void
    {
        $guardianUser = User::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Wali Murid Pencabut Izin',
            'email' => 'wali.cabut_' . Str::random(8) . '@example.com',
            'password_hash' => bcrypt('secret123'),
        ]);

        $relation = GuardianRelation::create([
            'id' => Str::uuid()->toString(),
            'learner_id' => $this->learner->id,
            'guardian_user_id' => $guardianUser->id,
            'status' => 'verified',
            'created_at' => now(),
        ]);

        $consent = $this->enrollmentService->grantConsent(
            $relation,
            $this->program,
            'PROGRAM_PARTICIPATION'
        );

        $policy = new ProgramPolicy();

        // 1. Sebelum dicabut: Wali berhak melihat progres ananda
        $this->assertTrue($policy->viewLearnerProgress($guardianUser, $this->program, $this->learner->id));

        // 2. Wali mencabut izin
        $this->enrollmentService->revokeConsent($consent);
        $this->assertNotNull($consent->fresh()->revoked_at);

        // 3. Seketika ditolak policy (TAT-24)
        $this->assertFalse($policy->viewLearnerProgress($guardianUser, $this->program, $this->learner->id));
    }
}
