<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\GuardianInvitation;
use App\Models\User;
use App\Services\AssignmentStateMachine;
use App\Services\LearnerEnrollmentService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class GuardianPortalController extends Controller
{
    public function __construct(
        protected LearnerEnrollmentService $enrollmentService,
        protected AssignmentStateMachine $fsm
    ) {}

    /**
     * Menampilkan halaman klaim undangan wali murid
     */
    public function showInvite(string $rawToken): Response
    {
        $tokenHash = hash('sha256', $rawToken);
        $invitation = GuardianInvitation::with(['guardianRelation.learner', 'program'])
            ->where('token_hash', $tokenHash)
            ->firstOrFail();

        if ($invitation->used_at || $invitation->expires_at->isPast()) {
            abort(410, 'Undangan ini sudah digunakan atau telah kedaluwarsa.');
        }

        return Inertia::render('Guardian/InviteClaim', [
            'raw_token' => $rawToken,
            'learner_name' => $invitation->guardianRelation->learner->display_name,
            'program_title' => $invitation->program->title,
        ]);
    }

    /**
     * Memproses klaim undangan orang tua & pencatatan persetujuan partisipasi (PDP)
     */
    public function claimInvite(Request $request, string $rawToken): RedirectResponse
    {
        $validated = $request->validate([
            'parent_name' => 'required|string|max:150',
            'phone_e164' => 'required|string|max:25',
            'relation_type' => 'required|in:parent,legal_guardian,caregiver',
        ]);

        $guardianUser = User::firstOrCreate(
            ['phone_e164' => $validated['phone_e164']],
            [
                'id' => Str::uuid()->toString(),
                'name' => $validated['parent_name'],
                'is_active' => true,
            ]
        );

        $relation = $this->enrollmentService->claimInvitation(
            $rawToken,
            $guardianUser,
            $validated['relation_type']
        );

        // Berikan persetujuan awal partisipasi program
        $invitation = GuardianInvitation::where('token_hash', hash('sha256', $rawToken))->first();
        if ($invitation) {
            $this->enrollmentService->grantConsent(
                $relation,
                $invitation->program,
                'PROGRAM_PARTICIPATION'
            );
        }

        return redirect()->route('dashboard')
            ->with('success', 'Undangan perwalian berhasil dikonfirmasi. Menunggu verifikasi pendidik.');
    }

    /**
     * Halaman web konfirmasi tugas rumah (Guardian Checkpoint)
     */
    public function showCheckpoint(string $assignmentId): Response
    {
        $assignment = Assignment::with(['mission', 'enrollment.learner', 'program'])->findOrFail($assignmentId);

        return Inertia::render('Guardian/Checkpoint', [
            'assignment' => [
                'id' => $assignment->id,
                'status' => $assignment->status,
                'mission_title' => $assignment->mission->title,
                'offline_guide' => $assignment->mission->offline_activity_guide,
                'learner_name' => $assignment->enrollment->learner->display_name,
                'program_title' => $assignment->program->title,
            ],
        ]);
    }

    /**
     * Merekam pilihan konfirmasi orang tua (Selesai / Belum / Butuh Bantuan) (TI-07, TAT-09)
     */
    public function confirmCheckpoint(Request $request, string $assignmentId): RedirectResponse
    {
        $assignment = Assignment::with(['enrollment.learner.guardianRelations'])->findOrFail($assignmentId);

        $validated = $request->validate([
            'response' => 'required|in:done,not_yet,need_help',
            'note' => 'nullable|string|max:500',
        ]);

        // Ambil relasi wali pertama yang terhubung ke siswa (atau buat fallback relasi)
        $relation = $assignment->enrollment->learner->guardianRelations()->first();
        if (!$relation) {
            $relation = $assignment->enrollment->learner->guardianRelations()->create([
                'id' => Str::uuid()->toString(),
                'relation_type' => 'parent',
                'status' => 'verified',
                'created_at' => now(),
            ]);
        }

        $this->fsm->recordGuardianConfirmation(
            $assignment,
            $relation,
            $validated['response'],
            $validated['note'] ?? null,
            'web_link'
        );

        return redirect()->back()->with('success', 'Konfirmasi tugas rumah berhasil dicatat. Terima kasih!');
    }
}
