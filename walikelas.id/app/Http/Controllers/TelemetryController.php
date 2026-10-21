<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TelemetryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TelemetryController extends Controller
{
    public function __construct(
        protected TelemetryService $telemetryService
    ) {}

    /**
     * Memulai sesi telemetri efisiensi kerja guru
     */
    public function start(Request $request): JsonResponse
    {
        $user = $request->user() ?: User::where('email', 'guru.budi@walikelas.id')->first() ?: User::first();

        $validated = $request->validate([
            'action_type' => 'required|in:triage_review,rubric_scoring,plan_generation,report_publishing',
            'program_id' => 'nullable|uuid|exists:learning_programs,id',
            'metadata' => 'nullable|array',
        ]);

        $session = $this->telemetryService->startSession(
            educator: $user,
            actionType: $validated['action_type'],
            programId: $validated['program_id'] ?? null,
            metadata: $validated['metadata'] ?? []
        );

        return response()->json([
            'status' => 'started',
            'session_id' => $session->id,
            'started_at' => $session->started_at,
        ]);
    }

    /**
     * Mengakhiri sesi telemetri kerja guru dan mencatat net time saved
     */
    public function end(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => 'required|uuid|exists:telemetry_sessions,id',
            'metadata' => 'nullable|array',
        ]);

        $session = $this->telemetryService->endSession(
            sessionId: $validated['session_id'],
            additionalMetadata: $validated['metadata'] ?? []
        );

        return response()->json([
            'status' => 'completed',
            'session' => $session,
        ]);
    }

    /**
     * Mengambil ringkasan metrik efisiensi
     */
    public function metrics(Request $request): JsonResponse
    {
        $programId = $request->query('program_id');
        $metrics = $this->telemetryService->getSummaryMetrics($programId);

        return response()->json($metrics);
    }

    /**
     * Mengunduh CSV data telemetri pilot untuk VC data room
     */
    public function exportCsv(Request $request): Response
    {
        $programId = $request->query('program_id');
        $csv = $this->telemetryService->exportCsv($programId);

        $filename = 'walikelas_pilot_telemetry_' . now()->format('Ymd_His') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
