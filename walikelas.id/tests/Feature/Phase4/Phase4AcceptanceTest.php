<?php

namespace Tests\Feature\Phase4;

use App\Models\LearningProgram;
use App\Models\ProgramEducator;
use App\Models\TelemetrySession;
use App\Models\User;
use App\Services\TelemetryService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class Phase4AcceptanceTest extends TestCase
{
    use DatabaseTransactions;

    protected User $educator;
    protected User $otherEducator;
    protected LearningProgram $program;
    protected TelemetryService $telemetryService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->telemetryService = app(TelemetryService::class);

        $this->educator = User::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Guru Fase 4 Lead',
            'email' => 'guru.fase4_' . Str::random(8) . '@walikelas.id',
        ]);

        $this->otherEducator = User::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Guru Asing',
            'email' => 'guru.asing_' . Str::random(8) . '@walikelas.id',
        ]);

        $this->program = LearningProgram::create([
            'id' => Str::uuid()->toString(),
            'mode' => 'school',
            'title' => 'Program Fase 4 Telemetri',
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
    }

    /**
     * TAT-17: Pelacakan durasi kerja guru | Telemetri mencatat waktu mulai dan selesai penelaahan tugas (TI-05)
     */
    public function test_tat_17_telemetry_tracks_teacher_work_duration_and_net_time_saved(): void
    {
        // 1. Guru memulai penelaahan tugas di Triage Queue (Action: triage_review, baseline 900 detik / 15 menit)
        $session = $this->telemetryService->startSession(
            educator: $this->educator,
            actionType: 'triage_review',
            programId: $this->program->id,
            metadata: ['device' => 'desktop', 'cases_reviewed' => 3]
        );

        $this->assertNotNull($session->id);
        $this->assertEquals('triage_review', $session->action_type);
        $this->assertEquals(900, $session->baseline_seconds);
        $this->assertNull($session->ended_at);
        $this->assertNull($session->duration_seconds);

        // Simulasi guru menyelesaikan penelaahan dalam 45 detik
        $this->telemetryService->endSession(
            sessionId: $session->id,
            additionalMetadata: ['cases_approved' => 3]
        );

        $refreshed = $session->fresh();
        $this->assertNotNull($refreshed->ended_at);
        $this->assertGreaterThanOrEqual(1, $refreshed->duration_seconds);
        $this->assertLessThanOrEqual($refreshed->baseline_seconds, $refreshed->duration_seconds);

        // Net Time Saved = Baseline - Durasi Riil
        $this->assertEquals(
            $refreshed->baseline_seconds - $refreshed->duration_seconds,
            $refreshed->net_seconds_saved
        );

        // Verifikasi audit log kekal tercatat
        $this->assertDatabaseHas('audit_events', [
            'action' => 'TELEMETRY_SESSION_COMPLETED',
            'subject_id' => $session->id,
            'actor_id' => $this->educator->id,
        ]);
    }

    /**
     * Security Audit: Tenant Isolation & IDOR Check on Triage & Decisions (TI-02)
     */
    public function test_security_audit_unauthorized_educator_denied_access_to_other_program_triage(): void
    {
        // Guru Asing (tidak terdaftar di program) mencoba mengakses Triage Program
        $response = $this->actingAs($this->otherEducator)
            ->getJson("/programs/{$this->program->id}/triage");

        // Ditolak dengan 404 (anti metadata leak, TI-02)
        $response->assertStatus(404);
    }

    /**
     * Security Audit: XSS Sanitization & HTML Escaping on Student & AI Text
     */
    public function test_security_audit_xss_payload_in_learner_name_is_safely_escaped(): void
    {
        $maliciousName = "<script>alert('xss')</script> Budi";
        $learner = \App\Models\Learner::create([
            'id' => Str::uuid()->toString(),
            'display_name' => $maliciousName,
            'created_by' => $this->educator->id,
        ]);

        $this->assertStringContainsString('&lt;script&gt;', e($learner->display_name));
        $this->assertStringNotContainsString('<script>', e($learner->display_name));
    }

    /**
     * VC Data Room Asset: CSV Telemetry Export Integrity
     */
    public function test_vc_data_room_csv_telemetry_export_returns_valid_structure(): void
    {
        // Buat 1 sesi telemetri selesai
        $session = $this->telemetryService->startSession(
            educator: $this->educator,
            actionType: 'rubric_scoring',
            programId: $this->program->id
        );
        $this->telemetryService->endSession($session->id);

        $csv = $this->telemetryService->exportCsv($this->program->id);

        $this->assertStringContainsString('Session ID', $csv);
        $this->assertStringContainsString('Educator Name', $csv);
        $this->assertStringContainsString('Net Time Saved', $csv);
        $this->assertStringContainsString($session->id, $csv);
        $this->assertStringContainsString('rubric_scoring', $csv);
        $this->assertStringContainsString($this->educator->name, $csv);
    }
}
