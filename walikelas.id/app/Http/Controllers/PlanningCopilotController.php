<?php

namespace App\Http\Controllers;

use App\Models\LearningProgram;
use App\Models\PlanVersion;
use App\Policies\ProgramPolicy;
use App\Services\PlanningCopilotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanningCopilotController extends Controller
{
    public function __construct(
        protected PlanningCopilotService $copilot
    ) {}

    /**
     * Ekstraksi rencana ajar dari teks bebas silabus/RPP (TI-04, TAT-03)
     */
    public function importText(Request $request, string $programId): RedirectResponse
    {
        $program = LearningProgram::findOrFail($programId);
        $user = Auth::user();

        $policy = new ProgramPolicy();
        if (!$policy->manage($user, $program)) {
            abort(404);
        }

        $validated = $request->validate([
            'raw_text' => 'required|string|min:10',
        ]);

        $planVersion = $this->copilot->importFromText(
            $program,
            $validated['raw_text'],
            $user
        );

        return redirect()->route('programs.show', $program->id)
            ->with('success', "Draf Rencana Versi {$planVersion->version_no} berhasil diekstraksi oleh Copilot.");
    }

    /**
     * Pembuatan rencana ajar melalui 5 langkah Wizard (TAT-04)
     */
    public function wizard(Request $request, string $programId): RedirectResponse
    {
        $program = LearningProgram::findOrFail($programId);
        $user = Auth::user();

        $policy = new ProgramPolicy();
        if (!$policy->manage($user, $program)) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'competency_codes' => 'required|array|min:1',
            'session_count' => 'required|integer|min:1|max:30',
            'objectives' => 'nullable|array',
            'activities' => 'nullable|array',
        ]);

        $planVersion = $this->copilot->generateFromWizard(
            $program,
            $validated,
            $user
        );

        return redirect()->route('programs.show', $program->id)
            ->with('success', "Draf Rencana Versi {$planVersion->version_no} berhasil disusun via Wizard.");
    }

    /**
     * Pengesahan resmi versi rencana ajar oleh pendidik pengampu (TI-04, TAT-05)
     */
    public function approve(Request $request, string $programId, string $versionId): RedirectResponse
    {
        $program = LearningProgram::findOrFail($programId);
        $version = PlanVersion::findOrFail($versionId);
        $user = Auth::user();

        $policy = new ProgramPolicy();
        if (!$policy->manage($user, $program)) {
            abort(404);
        }

        $this->copilot->approvePlanVersion($program, $version, $user);

        return redirect()->route('programs.show', $program->id)
            ->with('success', "Rencana Ajar Versi {$version->version_no} telah disahkan dan aktif.");
    }

    /**
     * Revisi rencana aktif menjadi versi baru N+1 (TI-04, TAT-23)
     */
    public function revise(Request $request, string $programId, string $versionId): RedirectResponse
    {
        $program = LearningProgram::findOrFail($programId);
        $version = PlanVersion::findOrFail($versionId);
        $user = Auth::user();

        $policy = new ProgramPolicy();
        if (!$policy->manage($user, $program)) {
            abort(404);
        }

        $validated = $request->validate([
            'content' => 'required|array',
        ]);

        $newVersion = $this->copilot->revisePlanVersion($program, $version, $validated['content'], $user);

        return redirect()->route('programs.show', $program->id)
            ->with('success', "Revisi berhasil dibuat sebagai Draf Versi {$newVersion->version_no}.");
    }
}
