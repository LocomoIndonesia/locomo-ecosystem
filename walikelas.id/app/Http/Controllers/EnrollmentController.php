<?php

namespace App\Http\Controllers;

use App\Models\Consent;
use App\Models\Enrollment;
use App\Models\GuardianRelation;
use App\Models\LearningProgram;
use App\Policies\ProgramPolicy;
use App\Services\LearnerEnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnrollmentController extends Controller
{
    public function __construct(
        protected LearnerEnrollmentService $enrollmentService
    ) {}

    /**
     * Mendaftarkan siswa baru ke program secara minimalis (Hanya Nama Panggilan)
     */
    public function storeLearner(Request $request, string $programId): RedirectResponse
    {
        $program = LearningProgram::findOrFail($programId);
        $user = Auth::user();

        $policy = new ProgramPolicy();
        if (!$policy->manage($user, $program)) {
            abort(404);
        }

        $validated = $request->validate([
            'display_name' => 'required|string|max:80',
            'grade_level' => 'nullable|integer|min:1|max:6',
            'avatar_id' => 'nullable|string',
        ]);

        $this->enrollmentService->enrollLearner(
            $program,
            $validated['display_name'],
            $validated['grade_level'] ?? 3,
            $validated['avatar_id'] ?? 'avatar_kancil_01',
            $user
        );

        return redirect()->route('programs.show', $program->id)
            ->with('success', "Siswa '{$validated['display_name']}' berhasil didaftarkan.");
    }

    /**
     * Membuat tautan undangan wali murid untuk dibagikan oleh pendidik
     */
    public function createInvite(Request $request, string $enrollmentId): RedirectResponse
    {
        $enrollment = Enrollment::with('program')->findOrFail($enrollmentId);
        $user = Auth::user();

        $policy = new ProgramPolicy();
        if (!$policy->manage($user, $enrollment->program)) {
            abort(404);
        }

        $result = $this->enrollmentService->createGuardianInvitation($enrollment, $user);

        return redirect()->back()->with('success', "Tautan undangan dibuat: {$result['invite_url']}");
    }

    /**
     * Pendidik memvalidasi hubungan perwalian secara dua sisi
     */
    public function verifyGuardian(Request $request, string $relationId): RedirectResponse
    {
        $relation = GuardianRelation::findOrFail($relationId);
        $user = Auth::user();

        $this->enrollmentService->verifyGuardian($relation, $user);

        return redirect()->back()->with('success', 'Wali murid berhasil diverifikasi.');
    }

    /**
     * Mencabut persetujuan (consent) perwalian (TAT-24)
     */
    public function revokeConsent(Request $request, string $consentId): RedirectResponse
    {
        $consent = Consent::with('guardianRelation')->findOrFail($consentId);

        $this->enrollmentService->revokeConsent($consent);

        return redirect()->back()->with('success', 'Persetujuan perwalian telah dicabut.');
    }
}
