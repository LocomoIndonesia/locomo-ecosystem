<?php

namespace App\Services;

use App\Models\AuditEvent;
use App\Models\Consent;
use App\Models\Enrollment;
use App\Models\GuardianInvitation;
use App\Models\GuardianRelation;
use App\Models\Learner;
use App\Models\LearningProgram;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;

class LearnerEnrollmentService
{
    /**
     * Mendaftarkan siswa secara minimalis (Hanya nama panggilan & avatar ramah anak - TI-01, TI-03)
     */
    public function enrollLearner(
        LearningProgram $program,
        string $displayName,
        int $gradeLevel = 3,
        ?string $avatarId = null,
        ?User $educator = null
    ): Enrollment {
        $learner = Learner::create([
            'id' => Str::uuid()->toString(),
            'display_name' => trim($displayName),
            'grade_level' => $gradeLevel,
            'avatar_id' => $avatarId ?? 'avatar_kancil_01',
            'is_synthetic' => false,
            'created_by' => $educator ? $educator->id : $program->created_by,
            'created_at' => now(),
        ]);

        $enrollment = Enrollment::create([
            'id' => Str::uuid()->toString(),
            'program_id' => $program->id,
            'learner_id' => $learner->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        AuditEvent::log(
            actorType: 'user',
            actorId: $educator?->id,
            action: 'LEARNER_ENROLLED',
            subjectType: 'enrollment',
            subjectId: $enrollment->id,
            programId: $program->id,
            payload: ['learner_id' => $learner->id, 'display_name' => $learner->display_name]
        );

        return $enrollment;
    }

    /**
     * Membuat tautan undangan wali murid yang aman & berbatas waktu
     */
    public function createGuardianInvitation(
        Enrollment $enrollment,
        User $educator,
        int $expiresInDays = 7
    ): array {
        // Buat relasi awal berstatus 'invited'
        $relation = GuardianRelation::create([
            'id' => Str::uuid()->toString(),
            'learner_id' => $enrollment->learner_id,
            'guardian_user_id' => null,
            'relation_type' => 'parent',
            'status' => 'invited',
            'created_at' => now(),
        ]);

        $rawToken = Str::random(40);
        $tokenHash = hash('sha256', $rawToken);

        $invitation = GuardianInvitation::create([
            'id' => Str::uuid()->toString(),
            'guardian_relation_id' => $relation->id,
            'program_id' => $enrollment->program_id,
            'token_hash' => $tokenHash,
            'expires_at' => now()->addDays($expiresInDays),
            'created_by' => $educator->id,
        ]);

        return [
            'invitation' => $invitation,
            'relation' => $relation,
            'raw_token' => $rawToken,
            'invite_url' => url("/guardian/invite/{$rawToken}"),
        ];
    }

    /**
     * Wali mengklaim undangan dan menghubungkan akunnya
     */
    public function claimInvitation(string $rawToken, User $guardianUser, string $relationType = 'parent'): GuardianRelation
    {
        $tokenHash = hash('sha256', $rawToken);

        $invitation = GuardianInvitation::with(['guardianRelation', 'program'])
            ->where('token_hash', $tokenHash)
            ->first();

        if (!$invitation || $invitation->used_at || $invitation->expires_at->isPast()) {
            throw new Exception('INVITATION_INVALID_OR_EXPIRED');
        }

        $relation = $invitation->guardianRelation;
        $relation->update([
            'guardian_user_id' => $guardianUser->id,
            'relation_type' => $relationType,
            'status' => 'claimed', // Masih butuh konfirmasi pendidik untuk 'verified'
        ]);

        $invitation->update(['used_at' => now()]);

        AuditEvent::log(
            actorType: 'user',
            actorId: $guardianUser->id,
            action: 'GUARDIAN_INVITATION_CLAIMED',
            subjectType: 'guardian_relation',
            subjectId: $relation->id,
            programId: $invitation->program_id,
            payload: ['relation_type' => $relationType]
        );

        return $relation->fresh();
    }

    /**
     * Pendidik memvalidasi hubungan perwalian secara dua sisi (Two-Sided Confirmation)
     */
    public function verifyGuardian(GuardianRelation $relation, User $educator): GuardianRelation
    {
        $relation->update([
            'status' => 'verified',
            'verified_via' => 'two_sided_educator_confirmation',
            'educator_confirmed_by' => $educator->id,
            'verified_at' => now(),
        ]);

        AuditEvent::log(
            actorType: 'user',
            actorId: $educator->id,
            action: 'GUARDIAN_VERIFIED',
            subjectType: 'guardian_relation',
            subjectId: $relation->id,
            programId: null,
            payload: ['educator_id' => $educator->id]
        );

        return $relation->fresh();
    }

    /**
     * Wali memberikan persetujuan (Consent) partisipasi dan laporan berkala (PDP Compliance)
     */
    public function grantConsent(
        GuardianRelation $relation,
        LearningProgram $program,
        string $purposeScope = 'PROGRAM_PARTICIPATION',
        string $policyVersion = 'v1.0'
    ): Consent {
        return Consent::firstOrCreate(
            [
                'guardian_relation_id' => $relation->id,
                'program_id' => $program->id,
                'purpose_scope' => $purposeScope,
                'revoked_at' => null,
            ],
            [
                'id' => Str::uuid()->toString(),
                'policy_version' => $policyVersion,
                'granted_at' => now(),
            ]
        );
    }

    /**
     * Wali mencabut izin partisipasi (TAT-24)
     */
    public function revokeConsent(Consent $consent): void
    {
        $consent->update([
            'revoked_at' => now(),
        ]);

        AuditEvent::log(
            actorType: 'user',
            actorId: $consent->guardianRelation->guardian_user_id,
            action: 'CONSENT_REVOKED',
            subjectType: 'consent',
            subjectId: $consent->id,
            programId: $consent->program_id,
            payload: ['purpose_scope' => $consent->purpose_scope]
        );
    }
}
