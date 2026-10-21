<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Enrollment;
use App\Models\LearningProgram;
use App\Models\Mission;
use App\Policies\ProgramPolicy;
use App\Services\AssignmentService;
use App\Services\LaunchTokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{
    public function __construct(
        protected AssignmentService $assignmentService,
        protected LaunchTokenService $launchTokenService
    ) {}

    /**
     * Menerbitkan misi belajar ke siswa (TI-11: Program wajib memiliki rencana yang disahkan)
     */
    public function store(Request $request, string $programId): RedirectResponse
    {
        $program = LearningProgram::with('activePlanVersion')->findOrFail($programId);
        $user = Auth::user();

        $policy = new ProgramPolicy();
        if (!$policy->manage($user, $program)) {
            abort(404);
        }

        $validated = $request->validate([
            'enrollment_id' => 'required|uuid',
            'mission_id' => 'required|uuid',
        ]);

        $enrollment = Enrollment::findOrFail($validated['enrollment_id']);
        $mission = Mission::findOrFail($validated['mission_id']);

        $assignment = $this->assignmentService->assignMission(
            $program,
            $enrollment,
            $mission,
            $user
        );

        // Langsung generate launch link
        $tokenData = $this->launchTokenService->generateLaunchToken($assignment);

        return redirect()->back()
            ->with('success', "Misi '{$mission->title}' berhasil ditugaskan. Tautan: {$tokenData['runner_url']}");
    }

    /**
     * Membuat ulang tautan peluncuran misi jika diperlukan
     */
    public function generateLink(Request $request, string $assignmentId): RedirectResponse
    {
        $assignment = Assignment::findOrFail($assignmentId);
        $tokenData = $this->launchTokenService->generateLaunchToken($assignment);

        return redirect()->back()
            ->with('success', "Tautan misi baru: {$tokenData['runner_url']}");
    }
}
