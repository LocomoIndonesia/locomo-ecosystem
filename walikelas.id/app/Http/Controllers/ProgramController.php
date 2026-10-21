<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\Competency;
use App\Models\LearningProgram;
use App\Models\Organization;
use App\Models\ProgramEducator;
use App\Policies\ProgramPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProgramController extends Controller
{
    /**
     * Membuat program pembelajaran baru (TI-01: Bebas institusi untuk privat)
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'mode' => 'required|in:school,private',
            'organization_id' => 'nullable|uuid',
            'goal_summary' => 'nullable|string|max:500',
            'grade_level' => 'nullable|integer|min:1|max:6',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date',
            'planned_session_count' => 'nullable|integer|min:1',
        ]);

        // TI-01: Jika privat, organisasi tidak boleh dipaksakan
        $orgId = $validated['mode'] === 'private' ? null : ($validated['organization_id'] ?? null);

        $program = LearningProgram::create([
            'id' => Str::uuid()->toString(),
            'mode' => $validated['mode'],
            'organization_id' => $orgId,
            'title' => $validated['title'],
            'goal_summary' => $validated['goal_summary'] ?? null,
            'grade_level' => $validated['grade_level'] ?? 3,
            'period_start' => $validated['period_start'] ?? null,
            'period_end' => $validated['period_end'] ?? null,
            'planned_session_count' => $validated['planned_session_count'] ?? 12,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        ProgramEducator::create([
            'id' => Str::uuid()->toString(),
            'program_id' => $program->id,
            'user_id' => $user->id,
            'role' => 'lead',
            'started_at' => now(),
        ]);

        AuditEvent::log(
            actorType: 'user',
            actorId: $user->id,
            action: 'PROGRAM_CREATED',
            subjectType: 'learning_program',
            subjectId: $program->id,
            programId: $program->id,
            payload: ['mode' => $program->mode, 'title' => $program->title]
        );

        return redirect()->route('programs.show', $program->id)
            ->with('success', 'Program berhasil dibuat.');
    }

    /**
     * Menampilkan ruang kerja (workspace) program pembelajaran terpadu
     */
    public function show(string $id): Response
    {
        $program = LearningProgram::with([
            'organization',
            'activePlanVersion',
            'learningPlan.versions' => fn ($q) => $q->orderBy('version_no', 'desc'),
            'enrollments.learner',
            'educators' => fn ($q) => $q->wherePivotNull('ended_at'),
        ])->findOrFail($id);

        $user = Auth::user();
        $policy = new ProgramPolicy();

        // TI-02: Jika tidak memiliki relasi sah, return 404 (mencegah leak metadata)
        if (!$policy->manage($user, $program)) {
            abort(404);
        }

        $competencies = Competency::with('rubrics')->get();

        return Inertia::render('Program/Workspace', [
            'program' => $program,
            'competencies' => $competencies,
            'organizations' => Organization::orderBy('name')->get(),
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ],
        ]);
    }
}
