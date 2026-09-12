<?php

namespace App\Services;

use App\Models\EscrowTransaction;
use App\Models\User;
use App\Models\ServiceOrder;
use App\Models\UserCheckin;
use Carbon\Carbon;
use Exception;

class QuestEvaluatorService
{
    /**
     * Standard parameter definitions for Quests.
     */
    public const ACTION_RECRUIT_MEMBERS = 'RECRUIT_MEMBERS';
    public const ACTION_REGISTER_MERCHANT_UNIMART = 'REGISTER_MERCHANT_UNIMART';
    public const ACTION_REGISTER_WALIKELAS_TEACHER = 'REGISTER_WALIKELAS_TEACHER';
    public const ACTION_DIGITAL_PPOB_TRANSACTION = 'DIGITAL_PPOB_TRANSACTION';
    public const ACTION_DAILY_CHECKIN_STREAK = 'DAILY_CHECKIN_STREAK';
    public const ACTION_MANUAL_PROOF_SUBMISSION = 'MANUAL_PROOF_SUBMISSION';

    /**
     * List of supported system action types with metadata.
     */
    public static function supportedActionTypes(): array
    {
        return [
            self::ACTION_RECRUIT_MEMBERS => [
                'code' => self::ACTION_RECRUIT_MEMBERS,
                'label' => 'Rekrut Anggota Baru (Referral)',
                'unit' => 'Anggota',
                'description' => 'Menghitung jumlah anggota baru yang mendaftar via referral pengguna sejak misi diambil.',
            ],
            self::ACTION_REGISTER_MERCHANT_UNIMART => [
                'code' => self::ACTION_REGISTER_MERCHANT_UNIMART,
                'label' => 'Daftarkan Toko / Bisnis ke Unimart POS',
                'unit' => 'Toko',
                'description' => 'Menghitung pendaftaran merchant aktif ke platform Unimart.group sejak misi diambil.',
            ],
            self::ACTION_REGISTER_WALIKELAS_TEACHER => [
                'code' => self::ACTION_REGISTER_WALIKELAS_TEACHER,
                'label' => 'Daftarkan Pendidik / Guru ke WaliKelas.id',
                'unit' => 'Guru',
                'description' => 'Menghitung registrasi profil pendidik ke ekosistem WaliKelas.id sejak misi diambil.',
            ],
            self::ACTION_DIGITAL_PPOB_TRANSACTION => [
                'code' => self::ACTION_DIGITAL_PPOB_TRANSACTION,
                'label' => 'Transaksi Produk Digital / PPOB',
                'unit' => 'Transaksi',
                'description' => 'Menghitung frekuensi transaksi pulsa/tagihan digital yang sukses sejak misi diambil.',
            ],
            self::ACTION_DAILY_CHECKIN_STREAK => [
                'code' => self::ACTION_DAILY_CHECKIN_STREAK,
                'label' => 'Check-in Harian Berturut-turut',
                'unit' => 'Hari',
                'description' => 'Menghitung jumlah check-in kehadiran saudagar sejak misi diambil.',
            ],
            self::ACTION_MANUAL_PROOF_SUBMISSION => [
                'code' => self::ACTION_MANUAL_PROOF_SUBMISSION,
                'label' => 'Verifikasi Bukti Khusus / Lapangan',
                'unit' => 'Verifikasi',
                'description' => 'Memerlukan verifikasi manual oleh Admin atau Saudagar Pembina.',
            ],
        ];
    }

    /**
     * Evaluasi progres terkini pengguna untuk transaksi escrow tertentu.
     */
    public static function evaluateProgress(EscrowTransaction $escrow): array
    {
        $quest = $escrow->quest;
        $params = $quest->parameters ?? [];
        $actionType = $params['action_type'] ?? self::ACTION_RECRUIT_MEMBERS;
        $targetThreshold = max(1, intval($params['target_threshold'] ?? 1));
        $userId = $escrow->user_id;
        $stakedAt = $escrow->created_at;

        $currentProgress = 0;

        switch ($actionType) {
            case self::ACTION_RECRUIT_MEMBERS:
                $currentProgress = User::where('upline_id', $userId)
                    ->where('created_at', '>=', $stakedAt)
                    ->count();
                break;

            case self::ACTION_REGISTER_MERCHANT_UNIMART:
                $currentProgress = ServiceOrder::where('user_id', $userId)
                    ->where('created_at', '>=', $stakedAt)
                    ->whereHas('ecosystemService', function ($q) {
                        $q->where('slug', 'like', '%unimart%');
                    })
                    ->whereIn('status', ['ACTIVE', 'COMPLETED', 'SUCCESS'])
                    ->count();
                break;

            case self::ACTION_REGISTER_WALIKELAS_TEACHER:
                $currentProgress = ServiceOrder::where('user_id', $userId)
                    ->where('created_at', '>=', $stakedAt)
                    ->whereHas('ecosystemService', function ($q) {
                        $q->where('slug', 'like', '%walikelas%');
                    })
                    ->whereIn('status', ['ACTIVE', 'COMPLETED', 'SUCCESS'])
                    ->count();
                break;

            case self::ACTION_DIGITAL_PPOB_TRANSACTION:
                $currentProgress = ServiceOrder::where('user_id', $userId)
                    ->where('created_at', '>=', $stakedAt)
                    ->where(function ($q) {
                        $q->where('order_type', 'PPOB')
                          ->orWhere('payment_type', 'PPOB');
                    })
                    ->whereIn('status', ['PAID', 'COMPLETED', 'SUCCESS'])
                    ->count();
                break;

            case self::ACTION_DAILY_CHECKIN_STREAK:
                $currentProgress = UserCheckin::where('user_id', $userId)
                    ->where('created_at', '>=', $stakedAt)
                    ->count();
                break;

            case self::ACTION_MANUAL_PROOF_SUBMISSION:
                $currentProgress = ($escrow->status === 'APPROVED_BY_ADMIN') ? $targetThreshold : 0;
                break;

            default:
                $currentProgress = 0;
                break;
        }

        $isCompleted = ($currentProgress >= $targetThreshold);
        $percentage = min(100, intval(($currentProgress / $targetThreshold) * 100));
        $isExpired = Carbon::now()->gt($escrow->expires_at);

        return [
            'action_type' => $actionType,
            'target_threshold' => $targetThreshold,
            'current_progress' => $currentProgress,
            'percentage' => $percentage,
            'is_completed' => $isCompleted,
            'is_expired' => $isExpired,
            'status' => $escrow->status,
            'expires_at' => $escrow->expires_at,
        ];
    }

    /**
     * Klaim reward misi atau tuntaskan kegagalan jika waktu habis.
     */
    public static function claimQuestReward(string $escrowId): array
    {
        $escrow = EscrowTransaction::with(['quest', 'user.wallet'])->findOrFail($escrowId);

        if ($escrow->status !== 'LOCKED') {
            throw new Exception("Misi ini sudah diselesaikan dengan status: {$escrow->status}.");
        }

        $eval = self::evaluateProgress($escrow);

        // Jika waktu kedaluwarsa dan target belum terpenuhi
        if ($eval['is_expired'] && !$eval['is_completed']) {
            $failResult = WalletService::resolveQuestFailed($escrow->id);
            return [
                'success' => false,
                'status' => 'FAILED',
                'message' => "Batas waktu misi telah berakhir sebelum target ({$eval['target_threshold']}) terpenuhi. Taruhan Koin Perak telah hangus sesuai regulasi.",
                'evaluation' => $eval,
                'wallet_result' => $failResult,
            ];
        }

        // Jika belum kedaluwarsa tapi target belum tercapai
        if (!$eval['is_completed']) {
            throw new Exception("Target misi belum tercapai! Capaian Anda saat ini: {$eval['current_progress']} dari target {$eval['target_threshold']}.");
        }

        // Target tercapai tepat waktu -> Resolve Success
        $successResult = WalletService::resolveQuestSuccess($escrow->id);

        return [
            'success' => true,
            'status' => 'SUCCESS',
            'message' => "Selamat! Misi berhasil diselesaikan! Modal Koin Perak Anda dikembalikan dan Koin Emas telah dicairkan ke dompet Anda.",
            'evaluation' => $eval,
            'wallet_result' => $successResult,
        ];
    }
}
