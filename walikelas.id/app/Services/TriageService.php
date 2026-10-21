<?php

namespace App\Services;

use App\Models\AiSuggestion;
use App\Models\Assignment;
use App\Models\Attempt;
use App\Models\GuardianConfirmation;
use App\Models\LearningProgram;
use App\Models\MasteryState;

class TriageService
{
    /**
     * Mengumpulkan antrean prioritas triage untuk pendidik dalam 1 program
     */
    public function getQueue(LearningProgram $program): array
    {
        $programId = $program->id;
        $enrollmentIds = $program->enrollments()->pluck('id');

        // 1. Alert Keamanan Siswa (Safety Circuit Breaker, TI-10) - Prioritas Tertinggi
        $safetyAlerts = Attempt::whereHas('assignment', function ($q) use ($programId) {
            $q->where('program_id', $programId);
        })
            ->whereHas('responses', function ($q) {
                $q->where('safety_flag', true);
            })
            ->with(['assignment.learner', 'assignment.mission', 'responses' => fn ($q) => $q->where('safety_flag', true)])
            ->orderByDesc('started_at')
            ->get()
            ->map(function ($attempt) {
                $flaggedResponse = $attempt->responses->first();
                return [
                    'id' => $attempt->id,
                    'type' => 'safety_alert',
                    'priority' => 'critical',
                    'learner_name' => $attempt->assignment->learner->display_name ?? 'Siswa',
                    'mission_title' => $attempt->assignment->mission->title ?? 'Misi',
                    'flagged_at' => $flaggedResponse?->created_at?->toIso8601String() ?? $attempt->started_at?->toIso8601String(),
                    'message' => 'Siswa mengetik kata kunci berisiko tinggi. Misi dijeda otomatis.',
                ];
            });

        // 2. Permintaan Bantuan dari Wali Murid (Offline Checkpoint need_help)
        $guardianRequests = GuardianConfirmation::whereHas('assignment', function ($q) use ($programId) {
            $q->where('program_id', $programId);
        })
            ->where('response', 'need_help')
            ->with(['assignment.enrollment.learner', 'assignment.mission', 'guardianRelation.user'])
            ->orderByDesc('confirmed_at')
            ->get()
            ->map(function ($gc) {
                return [
                    'id' => $gc->id,
                    'type' => 'guardian_help_request',
                    'priority' => 'high',
                    'learner_name' => $gc->assignment->enrollment->learner->display_name ?? 'Siswa',
                    'guardian_name' => $gc->guardianRelation->user->name ?? 'Wali Murid',
                    'mission_title' => $gc->assignment->mission->title ?? 'Tugas Rumah',
                    'created_at' => $gc->confirmed_at?->toIso8601String(),
                    'message' => 'Orang tua melaporkan anak mengalami kesulitan saat aktivitas dongeng di rumah.',
                ];
            });

        // 3. Tugas Terkumpul Menunggu Penilaian Guru
        $pendingSubmissions = Assignment::where('program_id', $programId)
            ->where('status', 'submitted')
            ->with(['enrollment.learner', 'mission', 'attempts.responses'])
            ->orderByDesc('updated_at')
            ->get()
            ->map(function ($assignment) {
                $responseCount = $assignment->attempts->flatMap->responses->count();
                return [
                    'id' => $assignment->id,
                    'type' => 'submission_pending_review',
                    'priority' => 'medium',
                    'learner_name' => $assignment->enrollment->learner->display_name ?? 'Siswa',
                    'mission_title' => $assignment->mission->title ?? 'Misi',
                    'submitted_at' => $assignment->updated_at?->toIso8601String(),
                    'response_count' => $responseCount,
                ];
            });

        // 4. Usulan Intervensi AI Menunggu Keputusan Pendidik (TI-05)
        $pendingAiSuggestions = AiSuggestion::where('program_id', $programId)
            ->where('status', 'pending')
            ->with(['enrollment.learner'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($sugg) {
                return [
                    'id' => $sugg->id,
                    'type' => 'ai_suggestion_pending',
                    'priority' => 'medium',
                    'kind' => $sugg->kind,
                    'learner_name' => $sugg->enrollment->learner->display_name ?? 'Siswa',
                    'rationale' => $sugg->rationale,
                    'payload' => $sugg->payload,
                    'uncertainty' => $sugg->uncertainty,
                    'created_at' => $sugg->created_at->toIso8601String(),
                ];
            });

        // 5. Siswa yang Butuh Dukungan (needs_support)
        $strugglingMasteries = MasteryState::whereIn('enrollment_id', $enrollmentIds)
            ->where('level', 'needs_support')
            ->with(['enrollment.learner', 'competency'])
            ->get()
            ->map(function ($mst) {
                return [
                    'id' => $mst->id,
                    'type' => 'learner_needs_support',
                    'priority' => 'medium',
                    'learner_name' => $mst->enrollment->learner->display_name ?? 'Siswa',
                    'competency_title' => $mst->competency->title ?? 'Kompetensi',
                    'competency_code' => $mst->competency->code ?? '',
                    'level' => $mst->level,
                ];
            });

        return [
            'total_items' => $safetyAlerts->count() + $guardianRequests->count() + $pendingSubmissions->count() + $pendingAiSuggestions->count(),
            'safety_alerts' => $safetyAlerts,
            'guardian_requests' => $guardianRequests,
            'pending_submissions' => $pendingSubmissions,
            'pending_ai_suggestions' => $pendingAiSuggestions,
            'struggling_masteries' => $strugglingMasteries,
        ];
    }
}
