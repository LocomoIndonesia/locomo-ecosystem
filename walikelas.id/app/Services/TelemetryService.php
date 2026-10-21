<?php

namespace App\Services;

use App\Models\AuditEvent;
use App\Models\LearningProgram;
use App\Models\TelemetrySession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TelemetryService
{
    /**
     * Baseline durasi cara konvensional dalam detik
     */
    protected array $baselines = [
        'triage_review' => 900,        // 15 menit penelaahan berkas manual
        'rubric_scoring' => 600,       // 10 menit penskoran esai manual per siswa
        'plan_generation' => 3600,     // 60 menit perancangan silabus/RPP manual
        'report_publishing' => 1200,   // 20 menit perangkuman laporan kemajuan per siswa
    ];

    /**
     * Memulai sesi telemetri kerja guru (TAT-17)
     */
    public function startSession(
        User $educator,
        string $actionType,
        ?string $programId = null,
        array $metadata = []
    ): TelemetrySession {
        $baseline = $this->baselines[$actionType] ?? 900;

        $session = TelemetrySession::create([
            'id' => Str::uuid()->toString(),
            'user_id' => $educator->id,
            'program_id' => $programId,
            'action_type' => $actionType,
            'started_at' => now(),
            'baseline_seconds' => $baseline,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);

        AuditEvent::log(
            actorType: 'educator',
            actorId: $educator->id,
            action: 'TELEMETRY_SESSION_STARTED',
            subjectType: 'telemetry_session',
            subjectId: $session->id,
            programId: $programId,
            payload: [
                'action_type' => $actionType,
                'baseline_seconds' => $baseline,
            ]
        );

        return $session;
    }

    /**
     * Mengakhiri sesi dan menghitung durasi kerja nyata serta net time saved (TAT-17)
     */
    public function endSession(
        string $sessionId,
        array $additionalMetadata = []
    ): TelemetrySession {
        $session = TelemetrySession::findOrFail($sessionId);

        $now = now();
        $durationSeconds = max(1, $now->diffInSeconds($session->started_at));
        $netSaved = max(0, $session->baseline_seconds - $durationSeconds);

        $mergedMetadata = array_merge($session->metadata ?? [], $additionalMetadata);

        $session->update([
            'ended_at' => $now,
            'duration_seconds' => $durationSeconds,
            'net_seconds_saved' => $netSaved,
            'metadata' => $mergedMetadata,
        ]);

        AuditEvent::log(
            actorType: 'educator',
            actorId: $session->user_id,
            action: 'TELEMETRY_SESSION_COMPLETED',
            subjectType: 'telemetry_session',
            subjectId: $session->id,
            programId: $session->program_id,
            payload: [
                'duration_seconds' => $durationSeconds,
                'baseline_seconds' => $session->baseline_seconds,
                'net_seconds_saved' => $netSaved,
            ]
        );

        return $session;
    }

    /**
     * Menghitung ringkasan metrik efisiensi kerja guru
     */
    public function getSummaryMetrics(?string $programId = null): array
    {
        $query = TelemetrySession::query()->whereNotNull('ended_at');
        if ($programId) {
            $query->where('program_id', $programId);
        }

        $sessions = $query->get();
        $totalSessions = $sessions->count();

        if ($totalSessions === 0) {
            return [
                'total_sessions' => 0,
                'total_duration_minutes' => 0,
                'total_baseline_minutes' => 0,
                'total_net_hours_saved' => 0,
                'average_efficiency_gain_pct' => 0,
            ];
        }

        $totalDurationSec = $sessions->sum('duration_seconds');
        $totalBaselineSec = $sessions->sum('baseline_seconds');
        $totalNetSavedSec = $sessions->sum('net_seconds_saved');

        $gainPct = $totalBaselineSec > 0
            ? round((($totalBaselineSec - $totalDurationSec) / $totalBaselineSec) * 100, 1)
            : 0;

        return [
            'total_sessions' => $totalSessions,
            'total_duration_minutes' => round($totalDurationSec / 60, 1),
            'total_baseline_minutes' => round($totalBaselineSec / 60, 1),
            'total_net_hours_saved' => round($totalNetSavedSec / 3600, 2),
            'average_efficiency_gain_pct' => max(0, $gainPct),
        ];
    }

    /**
     * Ekspor CSV data telemetri pilot untuk VC Pitch Deck Data Room
     */
    public function exportCsv(?string $programId = null): string
    {
        $query = TelemetrySession::with(['user', 'program'])->whereNotNull('ended_at')->orderByDesc('started_at');
        if ($programId) {
            $query->where('program_id', $programId);
        }

        $sessions = $query->get();

        $output = fopen('php://temp', 'r+');
        fputcsv($output, [
            'Session ID',
            'Educator Name',
            'Program Title',
            'Action Type',
            'Started At (UTC)',
            'Ended At (UTC)',
            'Duration (Seconds)',
            'Baseline Conventional (Seconds)',
            'Net Time Saved (Seconds)',
            'Net Time Saved (Minutes)',
            'Time Saved Percentage',
        ]);

        foreach ($sessions as $s) {
            $savedPct = $s->baseline_seconds > 0
                ? round(($s->net_seconds_saved / $s->baseline_seconds) * 100, 1) . '%'
                : '0%';

            fputcsv($output, [
                $s->id,
                $s->user->name ?? 'Educator',
                $s->program->title ?? 'All Programs',
                $s->action_type,
                $s->started_at->toIso8601String(),
                $s->ended_at?->toIso8601String(),
                $s->duration_seconds,
                $s->baseline_seconds,
                $s->net_seconds_saved,
                round($s->net_seconds_saved / 60, 2),
                $savedPct,
            ]);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }
}
