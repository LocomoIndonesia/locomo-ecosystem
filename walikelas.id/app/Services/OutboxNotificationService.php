<?php

namespace App\Services;

use App\Models\AuditEvent;
use App\Models\GuardianRelation;
use App\Models\LearningProgram;
use App\Models\OutboxMessage;
use Carbon\Carbon;
use Illuminate\Support\Str;

class OutboxNotificationService
{
    /**
     * Memasukkan pesan notifikasi ke outbox dengan penjaga jam tenang & verifikasi wali (TI-08, TI-09, TAT-08)
     */
    public function queueNotification(
        GuardianRelation $guardianRelation,
        LearningProgram $program,
        string $templateKey,
        string $channel = 'web_link',
        ?array $payload = []
    ): OutboxMessage {
        $policyLogs = [
            'checked_at' => now()->toIso8601String(),
            'guardian_relation_id' => $guardianRelation->id,
            'guardian_status' => $guardianRelation->status,
        ];

        // 1. TI-08: Pemeriksaan Status Verifikasi Perwalian
        if ($guardianRelation->status !== 'verified') {
            $policyLogs['policy_result'] = 'REJECTED_UNVERIFIED_GUARDIAN';
            $policyLogs['reason'] = 'Wali murid belum melewati konfirmasi dua sisi dengan pendidik.';

            $outbox = OutboxMessage::create([
                'id' => Str::uuid()->toString(),
                'guardian_relation_id' => $guardianRelation->id,
                'program_id' => $program->id,
                'channel' => $channel,
                'template_key' => $templateKey,
                'status' => 'blocked_policy', // TAT-08: Tercatat blocked_policy, tidak ada pesan keluar
                'policy_check' => $policyLogs,
                'created_at' => now(),
            ]);

            AuditEvent::log(
                actorType: 'system',
                actorId: null,
                action: 'NOTIFICATION_BLOCKED_BY_POLICY',
                subjectType: 'outbox_message',
                subjectId: $outbox->id,
                programId: $program->id,
                payload: $policyLogs
            );

            return $outbox;
        }

        // 2. Pemeriksaan Consent Aktif
        $hasActiveConsent = $guardianRelation->consents()
            ->where('program_id', $program->id)
            ->whereNull('revoked_at')
            ->exists();

        if (!$hasActiveConsent) {
            $policyLogs['policy_result'] = 'REJECTED_NO_ACTIVE_CONSENT';

            return OutboxMessage::create([
                'id' => Str::uuid()->toString(),
                'guardian_relation_id' => $guardianRelation->id,
                'program_id' => $program->id,
                'channel' => $channel,
                'template_key' => $templateKey,
                'status' => 'blocked_policy',
                'policy_check' => $policyLogs,
                'created_at' => now(),
            ]);
        }

        // 3. TI-09: Penjaga Jam Tenang (Quiet Hours 20.00 - 07.00 WIB)
        $nowWib = Carbon::now('Asia/Jakarta');
        $hour = $nowWib->hour;
        $isQuietHours = ($hour >= 20 || $hour < 7);

        if ($isQuietHours) {
            $policyLogs['policy_result'] = 'HELD_QUIET_HOURS';
            $policyLogs['reason'] = 'Pesan dijeda karena berada di dalam jam tenang (20.00 - 07.00 WIB).';

            // Jadwalkan untuk dikirim besok pukul 07.30 WIB
            $scheduledTime = $nowWib->copy();
            if ($hour >= 20) {
                $scheduledTime->addDay();
            }
            $scheduledTime->setTime(7, 30);

            return OutboxMessage::create([
                'id' => Str::uuid()->toString(),
                'guardian_relation_id' => $guardianRelation->id,
                'program_id' => $program->id,
                'channel' => $channel,
                'template_key' => $templateKey,
                'status' => 'held_quiet_hours',
                'policy_check' => $policyLogs,
                'scheduled_for' => $scheduledTime,
                'created_at' => now(),
            ]);
        }

        // 4. Jika lolos seluruh aturan, status link_ready
        $policyLogs['policy_result'] = 'APPROVED';

        return OutboxMessage::create([
            'id' => Str::uuid()->toString(),
            'guardian_relation_id' => $guardianRelation->id,
            'program_id' => $program->id,
            'channel' => $channel,
            'template_key' => $templateKey,
            'status' => 'link_ready',
            'policy_check' => $policyLogs,
            'created_at' => now(),
        ]);
    }
}
