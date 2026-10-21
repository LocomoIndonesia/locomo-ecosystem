<?php

namespace App\Services;

use App\Models\AuditEvent;
use App\Models\Enrollment;
use App\Models\LearningProgram;
use App\Models\MasteryState;
use App\Models\Report;
use App\Models\ReportSource;
use App\Models\Response as StudentResponse;
use App\Models\User;
use Illuminate\Support\Str;

class ProgressReportService
{
    public function __construct(
        protected GeminiService $geminiService
    ) {}

    /**
     * Menerbitkan draft atau laporan 3-lapis berversi (TI-12)
     */
    public function createReport(
        LearningProgram $program,
        Enrollment $enrollment,
        User $educator,
        bool $autoApprove = false
    ): Report {
        // Kumpulkan bukti dan data faktual siswa
        $assignments = $enrollment->assignments()->with('mission')->get();
        $completedCount = $assignments->where('status', 'submitted')->count();
        $attemptIds = \App\Models\Attempt::whereIn('assignment_id', $assignments->pluck('id'))->pluck('id');
        $responses = StudentResponse::whereIn('attempt_id', $attemptIds)->get();
        $masteryStates = MasteryState::where('enrollment_id', $enrollment->id)->with('competency')->get();

        $factsLayer = [
            'sessions_attended' => $assignments->count(),
            'missions_completed' => $completedCount,
            'completion_rate_percentage' => $assignments->count() > 0 ? round(($completedCount / $assignments->count()) * 100) : 0,
            'generated_at' => now()->toIso8601String(),
        ];

        $findingsLayer = [
            'competency_evaluations' => $masteryStates->map(function ($state) {
                return [
                    'code' => $state->competency->code,
                    'title' => $state->competency->title,
                    'level' => $state->level,
                    'validated_by_educator' => $state->decided_by_type === 'educator',
                    'evidence_count' => count($state->basis_refs ?? []),
                ];
            })->values()->toArray(),
        ];

        $recommendationsLayer = [
            'next_steps' => [
                'Pertahankan rutinitas membaca fabel 10 menit setiap hari.',
                'Tingkatkan eksplorasi kosakata baru melalui buku cerita bergambar.',
            ],
            'home_advice' => 'Ajak anak menceritakan kembali karakter favoritnya saat berkumpul bersama keluarga.',
        ];

        $attributionTags = [
            'human_note' => ['Observasi pendidik tercatat pada sesi tatap muka.'],
            'auto_assessment' => ['Asesmen otomatis pilihan ganda pada aplikasi siswa.'],
            'educator_validated' => $masteryStates->where('decided_by_type', 'educator')->pluck('competency.code')->toArray(),
        ];

        $reportId = Str::uuid()->toString();
        $status = $autoApprove ? 'approved' : 'draft';

        $report = Report::create([
            'id' => $reportId,
            'program_id' => $program->id,
            'enrollment_id' => $enrollment->id,
            'version_no' => 1,
            'status' => $status,
            'facts_layer' => $factsLayer,
            'findings_layer' => $findingsLayer,
            'recommendations_layer' => $recommendationsLayer,
            'attribution_tags' => $attributionTags,
            'approved_by' => $autoApprove ? $educator->id : null,
            'approved_at' => $autoApprove ? now() : null,
            'created_at' => now(),
        ]);

        // Catat dependensi ke report_sources (TI-12)
        foreach ($responses as $resp) {
            ReportSource::create([
                'report_id' => $reportId,
                'source_type' => 'responses',
                'source_id' => $resp->id,
            ]);
        }

        foreach ($masteryStates as $mst) {
            ReportSource::create([
                'report_id' => $reportId,
                'source_type' => 'mastery_states',
                'source_id' => $mst->id,
            ]);
        }

        AuditEvent::log(
            actorType: 'educator',
            actorId: $educator->id,
            action: $autoApprove ? 'PROGRESS_REPORT_APPROVED' : 'PROGRESS_REPORT_CREATED_DRAFT',
            subjectType: 'report',
            subjectId: $report->id,
            programId: $program->id,
            payload: ['status' => $status, 'enrollment_id' => $enrollment->id]
        );

        return $report;
    }

    /**
     * Menandai laporan lama menjadi 'needs_review' bila data asesmen yang mendasarinya dikoreksi (TI-12, TAT-12)
     */
    public function invalidateOnEvidenceChange(string $sourceType, string $sourceId): int
    {
        $matchingReportIds = ReportSource::where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->pluck('report_id');

        if ($matchingReportIds->isEmpty()) {
            return 0;
        }

        $affectedReports = Report::whereIn('id', $matchingReportIds)
            ->where('status', 'approved')
            ->get();

        foreach ($affectedReports as $rep) {
            $rep->update(['status' => 'needs_review']);

            AuditEvent::log(
                actorType: 'system',
                actorId: null,
                action: 'REPORT_MARKED_NEEDS_REVIEW',
                subjectType: 'report',
                subjectId: $rep->id,
                programId: $rep->program_id,
                payload: [
                    'invalidated_by_source_type' => $sourceType,
                    'invalidated_by_source_id' => $sourceId,
                    'previous_status' => 'approved',
                ]
            );
        }

        return $affectedReports->count();
    }
}
