<?php

namespace Tests\Feature\Phase1;

use App\Models\Competency;
use App\Models\Enrollment;
use App\Models\Learner;
use App\Models\LearningProgram;
use App\Models\Mission;
use App\Models\PlanVersion;
use App\Models\ProgramEducator;
use App\Models\User;
use App\Services\AssignmentService;
use App\Services\PlanningCopilotService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class Phase1AcceptanceTest extends TestCase
{
    use DatabaseTransactions;

    protected User $educator;
    protected LearningProgram $program;
    protected PlanningCopilotService $copilotService;
    protected AssignmentService $assignmentService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->copilotService = app(PlanningCopilotService::class);
        $this->assignmentService = app(AssignmentService::class);

        $this->educator = User::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Guru Pengampu Fase 1',
            'email' => 'guru.fase1_' . Str::random(8) . '@walikelas.id',
            'password_hash' => bcrypt('secret123'),
        ]);

        $this->program = LearningProgram::create([
            'id' => Str::uuid()->toString(),
            'mode' => 'school',
            'title' => 'Program Literasi Narasi Fase 1',
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
    }

    /**
     * TAT-03: Impor teks rencana pembelajaran bebas via Copilot (TI-04).
     * Ekstraksi menghasilkan draf berstatus 'draft' (tidak auto-approve).
     */
    public function test_tat_03_free_text_import_creates_draft_version_without_auto_approve(): void
    {
        $rawSyllabus = "Silabus Membaca Fabel Semester 1. Siswa mampu menemukan tokoh dan latar cerita kancil. 4 kali pertemuan tatap muka.";

        $planVersion = $this->copilotService->importFromText(
            $this->program,
            $rawSyllabus,
            $this->educator
        );

        $this->assertInstanceOf(PlanVersion::class, $planVersion);
        // TI-04: Seluruh ekstraksi AI WAJIB berstatus 'draft', dilarang auto-approve
        $this->assertEquals('draft', $planVersion->status);
        $this->assertNull($planVersion->approved_at);
        $this->assertNull($planVersion->approved_by);

        // Program belum memiliki active_plan_version_id
        $this->assertNull($this->program->fresh()->active_plan_version_id);

        $this->assertDatabaseHas('plan_versions', [
            'id' => $planVersion->id,
            'status' => 'draft',
            'source_type' => 'import',
        ]);

        $this->assertDatabaseHas('plan_sources', [
            'plan_version_id' => $planVersion->id,
            'kind' => 'pasted_text',
        ]);
    }

    /**
     * TAT-04: Penyusunan rencana via wizard (TAT-04).
     * Draf terisi dengan rubrik dan kompetensi dari pustaka terkurasi.
     */
    public function test_tat_04_wizard_path_builds_plan_from_curated_library(): void
    {
        $wizardData = [
            'title' => 'Rencana Wizard Fase B Literasi',
            'grade_level' => 3,
            'competency_codes' => ['LIT-B-EXP-01', 'LIT-B-SEQ-02'],
            'session_count' => 8,
            'objectives' => [
                'Menemukan informasi tersurat tokoh fabel (LIT-B-EXP-01)',
                'Mengurutkan alur cerita fabel secara runtut (LIT-B-SEQ-02)',
            ],
        ];

        $planVersion = $this->copilotService->generateFromWizard(
            $this->program,
            $wizardData,
            $this->educator
        );

        $this->assertInstanceOf(PlanVersion::class, $planVersion);
        $this->assertEquals('draft', $planVersion->status);
        $this->assertEquals('wizard', $planVersion->source_type);
        $this->assertEquals(['LIT-B-EXP-01', 'LIT-B-SEQ-02'], $planVersion->content['competency_codes']);
        $this->assertCount(2, $planVersion->content['objectives']);
    }

    /**
     * TAT-05: Menerbitkan tugas pada program draft / rencana belum disahkan (TI-11).
     * Ditolak dengan error PLAN_NOT_APPROVED.
     */
    public function test_tat_05_assigning_mission_on_draft_plan_rejected_with_error(): void
    {
        $learner = Learner::create([
            'id' => Str::uuid()->toString(),
            'display_name' => 'Siswa Tes Guardrail',
            'grade_level' => 3,
            'created_by' => $this->educator->id,
        ]);

        $enrollment = Enrollment::create([
            'id' => Str::uuid()->toString(),
            'program_id' => $this->program->id,
            'learner_id' => $learner->id,
            'status' => 'active',
        ]);

        $mission = Mission::first();
        $this->assertNotNull($mission, 'Seed data mission must exist.');

        // 1. Program masih draft tanpa active_plan_version yang disahkan
        $this->assertNull($this->program->active_plan_version_id);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('PLAN_NOT_APPROVED');

        $this->assignmentService->assignMission(
            $this->program,
            $enrollment,
            $mission,
            $this->educator
        );
    }

    /**
     * TAT-23: Revisi rencana aktif (TI-04, TAT-23).
     * Dibuat record plan_versions baru (N+1); tugas lama tetap merujuk versi lama.
     */
    public function test_tat_23_plan_revision_creates_new_version_preserving_assignment_bindings(): void
    {
        // 1. Buat versi 1 dan sahkan
        $rawSyllabus = "Silabus Awal Versi 1";
        $version1 = $this->copilotService->importFromText($this->program, $rawSyllabus, $this->educator);
        $approvedVersion1 = $this->copilotService->approvePlanVersion($this->program, $version1, $this->educator);

        $this->assertEquals('approved', $approvedVersion1->status);
        $this->assertEquals($approvedVersion1->id, $this->program->fresh()->active_plan_version_id);

        // 2. Terbitkan tugas yang terikat pada Versi 1
        $learner = Learner::create([
            'id' => Str::uuid()->toString(),
            'display_name' => 'Siswa Penguji Revisi',
            'grade_level' => 3,
            'created_by' => $this->educator->id,
        ]);

        $enrollment = Enrollment::create([
            'id' => Str::uuid()->toString(),
            'program_id' => $this->program->id,
            'learner_id' => $learner->id,
            'status' => 'active',
        ]);

        $mission = Mission::first();
        $assignmentV1 = $this->assignmentService->assignMission(
            $this->program->fresh(),
            $enrollment,
            $mission,
            $this->educator
        );

        $this->assertEquals($approvedVersion1->id, $assignmentV1->plan_version_id);

        // 3. Lakukan revisi rencana aktif -> Dibuat versi 2 (draft)
        $newContent = [
            'title' => 'Rencana Revisi Versi 2',
            'objectives' => ['Tujuan Baru Versi 2'],
        ];

        $version2Draft = $this->copilotService->revisePlanVersion(
            $this->program->fresh(),
            $approvedVersion1,
            $newContent,
            $this->educator
        );

        $this->assertEquals(2, $version2Draft->version_no);
        $this->assertEquals('draft', $version2Draft->status);

        // 4. Verifikasi Invarian TI-04:
        // Program tetap mempertahankan active_plan_version_id ke Versi 1 sampai Versi 2 resmi disahkan
        $this->assertEquals($approvedVersion1->id, $this->program->fresh()->active_plan_version_id);

        // Tugas yang sudah diterbitkan tetap mengikat ke Versi 1 (immutable snapshot)
        $this->assertEquals($approvedVersion1->id, $assignmentV1->fresh()->plan_version_id);
    }
}
