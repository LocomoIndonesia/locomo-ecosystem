<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\AuditEvent;
use App\Models\LaunchToken;
use Exception;
use Illuminate\Support\Str;

class LaunchTokenService
{
    /**
     * Membuat tautan peluncuran misi belajar digital untuk siswa (Akses instan tanpa password)
     */
    public function generateLaunchToken(Assignment $assignment, int $validHours = 48): array
    {
        $rawToken = Str::random(40);
        $tokenHash = hash('sha256', $rawToken);

        $launchToken = LaunchToken::create([
            'id' => Str::uuid()->toString(),
            'assignment_id' => $assignment->id,
            'token_hash' => $tokenHash,
            'expires_at' => now()->addHours($validHours),
            'created_at' => now(),
        ]);

        AuditEvent::log(
            actorType: 'system',
            actorId: null,
            action: 'LAUNCH_TOKEN_GENERATED',
            subjectType: 'launch_token',
            subjectId: $launchToken->id,
            programId: $assignment->program_id,
            payload: ['assignment_id' => $assignment->id, 'valid_hours' => $validHours]
        );

        return [
            'launch_token' => $launchToken,
            'raw_token' => $rawToken,
            'runner_url' => url("/runner/{$rawToken}"),
        ];
    }

    /**
     * Memvalidasi token peluncuran misi membaca anak (TAT-22)
     * Ditolak HTTP 401/404 jika token tidak ada, sudah dicabut, atau kedaluwarsa.
     */
    public function validateToken(string $rawToken): Assignment
    {
        $tokenHash = hash('sha256', $rawToken);

        $token = LaunchToken::with(['assignment.mission', 'assignment.enrollment.learner', 'assignment.program'])
            ->where('token_hash', $tokenHash)
            ->first();

        if (!$token || $token->revoked_at || $token->expires_at->isPast()) {
            abort(404, 'TOKEN_EXPIRED_OR_INVALID');
        }

        return $token->assignment;
    }

    /**
     * Mencabut token aktif sehingga tidak dapat digunakan kembali
     */
    public function revokeToken(LaunchToken $token): void
    {
        $token->update(['revoked_at' => now()]);
    }
}
