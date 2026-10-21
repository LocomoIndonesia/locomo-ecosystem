<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Attempt;
use App\Services\AssignmentStateMachine;
use App\Services\LaunchTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LearnerRunnerController extends Controller
{
    public function __construct(
        protected LaunchTokenService $tokenService,
        protected AssignmentStateMachine $fsm
    ) {}

    /**
     * Membuka antarmuka membaca ramah anak berbasis Server-Driven UI (TAT-22: validasi token)
     */
    public function show(string $rawToken): Response
    {
        $assignment = $this->tokenService->validateToken($rawToken);

        // Cari atau mulai attempt aktif
        $attempt = $assignment->attempts()->whereNull('finished_at')->latest('started_at')->first();
        if (!$attempt) {
            $attempt = $this->fsm->startMission($assignment);
        }

        return Inertia::render('Learner/Runner', [
            'raw_token' => $rawToken,
            'assignment' => [
                'id' => $assignment->id,
                'status' => $assignment->status,
                'requires_guardian' => $assignment->requires_guardian_confirmation || $assignment->mission->kind === 'home',
            ],
            'learner' => [
                'display_name' => $assignment->enrollment->learner->display_name,
                'avatar_id' => $assignment->enrollment->learner->avatar_id,
            ],
            'mission' => [
                'id' => $assignment->mission->id,
                'title' => $assignment->mission->title,
                'kind' => $assignment->mission->kind,
                'estimated_minutes' => $assignment->mission->estimated_minutes,
                'steps' => $assignment->mission->steps_payload,
            ],
            'attempt_id' => $attempt->id,
        ]);
    }

    /**
     * Merekam jawaban siswa per langkah & memicu safeguarding jika ada kata bahaya (TI-10, TAT-16)
     */
    public function submitStep(Request $request, string $rawToken): JsonResponse
    {
        $assignment = $this->tokenService->validateToken($rawToken);
        $attempt = Attempt::findOrFail($request->input('attempt_id'));

        $validated = $request->validate([
            'step_key' => 'required|string',
            'answer' => 'required',
            'hints_used' => 'nullable|integer',
            'auto_result' => 'nullable|string',
        ]);

        $response = $this->fsm->recordStepResponse(
            $attempt,
            $validated['step_key'],
            is_array($validated['answer']) ? $validated['answer'] : ['value' => $validated['answer']],
            $validated['hints_used'] ?? 0,
            $validated['auto_result'] ?? null
        );

        return response()->json([
            'status' => 'ok',
            'safety_flag' => $response->safety_flag,
            'response_id' => $response->id,
        ]);
    }

    /**
     * Menyelesaikan seluruh langkah digital dan menampilkan layar jeda layar (TAT-13)
     */
    public function finish(Request $request, string $rawToken): JsonResponse
    {
        $assignment = $this->tokenService->validateToken($rawToken);
        $attempt = Attempt::findOrFail($request->input('attempt_id'));

        $this->fsm->completeDigitalSteps(
            $assignment,
            $attempt,
            $request->input('active_seconds', 300)
        );

        return response()->json([
            'status' => 'completed',
            'next_state' => $assignment->fresh()->status,
        ]);
    }
}
