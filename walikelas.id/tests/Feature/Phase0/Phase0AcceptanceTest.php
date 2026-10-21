<?php

namespace Tests\Feature\Phase0;

use App\Models\AuditEvent;
use App\Models\Enrollment;
use App\Models\Learner;
use App\Models\LearningProgram;
use App\Models\MasteryState;
use App\Models\Organization;
use App\Models\ProgramEducator;
use App\Models\User;
use App\Policies\MasteryStatePolicy;
use App\Policies\ProgramPolicy;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class Phase0AcceptanceTest extends TestCase
{
    /**
     * TAT-01: Tutor privat membuat program tanpa institusi (TI-01).
     * Sukses tersimpan; form & model tidak mewajibkan organization_id.
     */
    public function test_tat_01_private_tutor_creates_program_without_organization(): void
    {
        $tutor = User::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Kak Dian - Tutor Privat',
            'email' => 'dian.tutor@example.com',
            'password_hash' => bcrypt('secret123'),
        ]);

        $program = LearningProgram::create([
            'id' => Str::uuid()->toString(),
            'mode' => 'private',
            'organization_id' => null, // Bebas institusi / NPSN
            'title' => 'Pendampingan Membaca Intensif Privat',
            'grade_level' => 3,
            'status' => 'draft',
            'created_by' => $tutor->id,
        ]);

        ProgramEducator::create([
            'id' => Str::uuid()->toString(),
            'program_id' => $program->id,
            'user_id' => $tutor->id,
            'role' => 'lead',
            'started_at' => now(),
        ]);

        $this->assertDatabaseHas('learning_programs', [
            'id' => $program->id,
            'mode' => 'private',
            'organization_id' => null,
            'title' => 'Pendampingan Membaca Intensif Privat',
        ]);

        $this->assertNull($program->organization_id);
        $this->assertTrue($tutor->activePrograms->contains('id', $program->id));
    }

    /**
     * TAT-02: Pendidik beralih antara program sekolah & privat (TI-01, TI-02).
     * Workspace mengizinkan pendidik mengampu kedua mode secara terisolasi.
     */
    public function test_tat_02_educator_switches_between_school_and_private_modes(): void
    {
        $educator = User::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Pak Joko Multi-Mode',
            'email' => 'joko.multimode@example.com',
            'password_hash' => bcrypt('secret123'),
        ]);

        $school = Organization::create([
            'id' => Str::uuid()->toString(),
            'kind' => 'school',
            'name' => 'SD Harapan Bangsa',
            'npsn' => '20109988',
            'created_by' => $educator->id,
        ]);

        // Program Mode Sekolah
        $schoolProgram = LearningProgram::create([
            'id' => Str::uuid()->toString(),
            'mode' => 'school',
            'organization_id' => $school->id,
            'title' => 'Kelas 3B Literasi Sekolah',
            'created_by' => $educator->id,
        ]);

        ProgramEducator::create([
            'id' => Str::uuid()->toString(),
            'program_id' => $schoolProgram->id,
            'user_id' => $educator->id,
            'role' => 'lead',
            'started_at' => now(),
        ]);

        // Program Mode Privat
        $privateProgram = LearningProgram::create([
            'id' => Str::uuid()->toString(),
            'mode' => 'private',
            'organization_id' => null,
            'title' => 'Klub Membaca Sore Privat',
            'created_by' => $educator->id,
        ]);

        ProgramEducator::create([
            'id' => Str::uuid()->toString(),
            'program_id' => $privateProgram->id,
            'user_id' => $educator->id,
            'role' => 'lead',
            'started_at' => now(),
        ]);

        $policy = new ProgramPolicy();

        // Pendidik dapat mengelola kedua program
        $this->assertTrue($policy->manage($educator, $schoolProgram));
        $this->assertTrue($policy->manage($educator, $privateProgram));

        // Program memiliki tipe mode yang tepat
        $this->assertEquals('school', $schoolProgram->mode);
        $this->assertEquals($school->id, $schoolProgram->organization_id);

        $this->assertEquals('private', $privateProgram->mode);
        $this->assertNull($privateProgram->organization_id);
    }

    /**
     * TAT-07: Guru B mencoba mengakses data siswa program A (TI-02).
     * Ditolak oleh relasi policy, menjamin isolasi tenant antar program.
     */
    public function test_tat_07_tenant_isolation_denies_cross_program_access(): void
    {
        $educatorA = User::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Guru A (Sekolah Timur)',
            'email' => 'guru.a@example.com',
            'password_hash' => bcrypt('secret123'),
        ]);

        $educatorB = User::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Guru B (Sekolah Barat)',
            'email' => 'guru.b@example.com',
            'password_hash' => bcrypt('secret123'),
        ]);

        $programA = LearningProgram::create([
            'id' => Str::uuid()->toString(),
            'mode' => 'school',
            'title' => 'Program Literasi A',
            'created_by' => $educatorA->id,
        ]);

        ProgramEducator::create([
            'id' => Str::uuid()->toString(),
            'program_id' => $programA->id,
            'user_id' => $educatorA->id,
            'role' => 'lead',
            'started_at' => now(),
        ]);

        $learnerA = Learner::create([
            'id' => Str::uuid()->toString(),
            'display_name' => 'Siswa Kelas A',
            'grade_level' => 3,
            'created_by' => $educatorA->id,
        ]);

        Enrollment::create([
            'id' => Str::uuid()->toString(),
            'program_id' => $programA->id,
            'learner_id' => $learnerA->id,
            'status' => 'active',
        ]);

        $policy = new ProgramPolicy();

        // Guru A memiliki wewenang
        $this->assertTrue($policy->manage($educatorA, $programA));
        $this->assertTrue($policy->viewLearnerProgress($educatorA, $programA, $learnerA->id));

        // Guru B ditolak akses ke Program A dan data siswa A (TI-02)
        $this->assertFalse($policy->manage($educatorB, $programA));
        $this->assertFalse($policy->viewLearnerProgress($educatorB, $programA, $learnerA->id));
    }

    /**
     * TAT-19: Percobaan set 'educator_validated' tanpa pendidik sah ditolak (TI-06).
     * Ditolak di level policy dan database check constraint.
     */
    public function test_tat_19_mastery_validation_constraint_and_policy(): void
    {
        $educator = User::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Guru Validasi',
            'email' => 'guru.validasi@example.com',
            'password_hash' => bcrypt('secret123'),
        ]);

        $nonEducator = User::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Orang Tua / Non-Pendidik',
            'email' => 'wali.murid@example.com',
            'password_hash' => bcrypt('secret123'),
        ]);

        $program = LearningProgram::create([
            'id' => Str::uuid()->toString(),
            'mode' => 'school',
            'title' => 'Program Validasi Capaian',
            'created_by' => $educator->id,
        ]);

        ProgramEducator::create([
            'id' => Str::uuid()->toString(),
            'program_id' => $program->id,
            'user_id' => $educator->id,
            'role' => 'lead',
            'started_at' => now(),
        ]);

        $learner = Learner::create([
            'id' => Str::uuid()->toString(),
            'display_name' => 'Siswa Validasi',
            'grade_level' => 3,
            'created_by' => $educator->id,
        ]);

        $enrollment = Enrollment::create([
            'id' => Str::uuid()->toString(),
            'program_id' => $program->id,
            'learner_id' => $learner->id,
            'status' => 'active',
        ]);

        $competencyId = DB::table('competencies')->value('id');

        // 1. Uji Policy: Hanya pendidik pengampu yang diizinkan memvalidasi
        $masteryPolicy = new MasteryStatePolicy();
        $this->assertTrue($masteryPolicy->validateMastery($educator, $enrollment));
        $this->assertFalse($masteryPolicy->validateMastery($nonEducator, $enrollment));

        // 2. Uji Database Check Constraint:
        // Level 'educator_validated' dengan decided_by_type = 'system' HARUS DITOLAK
        $this->expectException(QueryException::class);

        MasteryState::create([
            'id' => Str::uuid()->toString(),
            'enrollment_id' => $enrollment->id,
            'competency_id' => $competencyId,
            'level' => 'educator_validated',
            'basis_refs' => ['source' => 'auto_scoring'],
            'decided_by_type' => 'system', // Ilegal: harus educator
            'decided_by' => null,          // Ilegal: harus ada pendidik penanggung jawab
        ]);
    }

    /**
     * TAT-20: Percobaan UPDATE atau DELETE pada audit_events ditolak (TI-13).
     * Ditolak oleh PostgreSQL immutability trigger trigger_prevent_audit_events_mutation.
     */
    public function test_tat_20_audit_events_table_is_append_only_and_immutable(): void
    {
        $audit = AuditEvent::log(
            actorType: 'system',
            actorId: null,
            action: 'PHASE_0_SECURITY_AUDIT_LOG_TEST',
            payload: ['integrity' => 'strict']
        );

        $this->assertDatabaseHas('audit_events', [
            'id' => $audit->id,
            'action' => 'PHASE_0_SECURITY_AUDIT_LOG_TEST',
        ]);

        // 1. Percobaan UPDATE pada tabel audit_events harus dilempar Exception oleh Postgres Trigger
        $updateFailed = false;
        try {
            DB::table('audit_events')
                ->where('id', $audit->id)
                ->update(['action' => 'TAMPERED_ACTION']);
        } catch (QueryException $e) {
            $updateFailed = true;
            $this->assertStringContainsString('audit_events is append-only', $e->getMessage());
        }
        $this->assertTrue($updateFailed, 'PostgreSQL Trigger failed to block UPDATE on audit_events');

        // 2. Percobaan DELETE pada tabel audit_events harus dilempar Exception oleh Postgres Trigger
        $deleteFailed = false;
        try {
            DB::table('audit_events')
                ->where('id', $audit->id)
                ->delete();
        } catch (QueryException $e) {
            $deleteFailed = true;
            $this->assertStringContainsString('audit_events is append-only', $e->getMessage());
        }
        $this->assertTrue($deleteFailed, 'PostgreSQL Trigger failed to block DELETE on audit_events');
    }
}
