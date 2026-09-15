<?php

namespace App\Services;

use App\Models\MissionVerification;
use App\Models\MissionVerificationJuror;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MissionVerificationService
{
    protected ReputationService $reputationService;
    protected WebPushService $webPushService;

    public function __construct(ReputationService $reputationService, WebPushService $webPushService)
    {
        $this->reputationService = $reputationService;
        $this->webPushService = $webPushService;
    }

    /**
     * Submit pengajuan bukti misi baru oleh penjelajah/saudagar
     */
    public function submitProof(User $submitter, array $data): MissionVerification
    {
        return DB::transaction(function () use ($submitter, $data) {
            $verification = MissionVerification::create([
                'quest_id' => $data['quest_id'] ?? null,
                'submitter_user_id' => $submitter->id,
                'mission_title' => $data['mission_title'] ?? 'Misi Ekspedisi Khusus',
                'proof_image_url' => $data['proof_image_url'] ?? null,
                'proof_description' => $data['proof_description'] ?? 'Tidak ada deskripsi tambahan.',
                'reward_gold' => (int) ($data['reward_gold'] ?? 50),
                'reward_silver' => (int) ($data['reward_silver'] ?? 500),
                'is_honeypot' => (bool) ($data['is_honeypot'] ?? false),
                'honeypot_correct_verdict' => $data['honeypot_correct_verdict'] ?? 'REJECT',
                'status' => 'IN_VOTING',
                'valid_votes_count' => 0,
                'reject_votes_count' => 0,
                'total_jurors_count' => 5,
            ]);

            // Otomatis tunjuk 5 juri independen
            $this->assignJurors($verification, 5);

            return $verification;
        });
    }

    /**
     * Algoritma seleksi acak 5 juri independen (Anti-Kolusi & Reputasi)
     */
    public function assignJurors(MissionVerification $verification, int $count = 5): Collection
    {
        $submitter = $verification->submitter;

        // 1. Identifikasi daftar pengguna yang dilarang menjadi juri (Anti-Kolusi)
        // Dilarang: Pembuat bukti itu sendiri, upline langsung, dan grand upline
        $excludedUserIds = array_filter([
            $submitter->id,
            $submitter->upline_id,
            $submitter->grand_upline_id,
        ]);

        // Dilarang: Downline langsung & grand downline dari pembuat misi
        $downlineIds = User::where('upline_id', $submitter->id)
            ->orWhere('grand_upline_id', $submitter->id)
            ->pluck('id')
            ->toArray();
        $excludedUserIds = array_unique(array_merge($excludedUserIds, $downlineIds));

        // 2. Query kandidat juri yang memenuhi kualifikasi
        // Syarat: license_status = ACTIVE, tidak sedang banned juri, bukan anggota kolusi
        $candidateQuery = User::query()
            ->whereNotIn('id', $excludedUserIds)
            ->where('license_status', 'ACTIVE')
            ->where(function ($query) {
                $query->whereNull('jury_banned_until')
                    ->orWhere('jury_banned_until', '<=', now());
            });

        // Coba prioritaskan kandidat yang memiliki reputasi >= 250 jika cukup
        $highRepCandidates = (clone $candidateQuery)
            ->whereHas('reputation', function ($q) {
                $q->where('total_score', '>=', 250);
            })
            ->inRandomOrder()
            ->take($count)
            ->get();

        $selectedJurors = $highRepCandidates;

        // Jika jumlah kandidat berreputasi tinggi kurang dari target, ambil sisa kandidat aktif lain
        if ($selectedJurors->count() < $count) {
            $alreadySelectedIds = $selectedJurors->pluck('id')->toArray();
            $fallbackNeeded = $count - $selectedJurors->count();

            $fallbackJurors = $candidateQuery
                ->whereNotIn('id', array_merge($excludedUserIds, $alreadySelectedIds))
                ->inRandomOrder()
                ->take($fallbackNeeded)
                ->get();

            $selectedJurors = $selectedJurors->merge($fallbackJurors);
        }

        // 3. Buat record penugasan juri
        $createdJurors = new Collection();
        foreach ($selectedJurors as $jurorUser) {
            $jurorRecord = MissionVerificationJuror::updateOrCreate(
                [
                    'verification_id' => $verification->id,
                    'juror_user_id' => $jurorUser->id,
                ],
                [
                    'vote' => 'PENDING',
                    'stake_amount_silver' => 50,
                    'reward_amount_silver' => 0,
                    'is_slashed' => false,
                ]
            );
            $createdJurors->push($jurorRecord);

            // 4. Kirim Real-Time WebPush ke masing-masing juri
            try {
                $this->webPushService->sendToUser(
                    $jurorUser,
                    '🛡️ Panggilan Tugas Juri PitiCoin',
                    'Anda terpilih sebagai 1 dari 5 juri independen untuk memvalidasi misi: ' . $verification->mission_title,
                    [
                        'url' => '/verifications/' . $verification->id,
                        'type' => 'jury_call',
                        'verification_id' => $verification->id,
                    ]
                );
            } catch (\Throwable $e) {
                Log::warning("Gagal mengirim WebPush ke juri {$jurorUser->id}: " . $e->getMessage());
            }
        }

        return $createdJurors;
    }

    /**
     * Eksekusi pemberian suara juri dengan pasak staking 50 Koin Perak
     */
    public function castVote(User $juror, MissionVerification $verification, string $vote, ?string $notes = null): array
    {
        $vote = strtoupper(trim($vote));
        if (!in_array($vote, ['VALID', 'REJECT'], true)) {
            throw new \InvalidArgumentException("Pilihan vote tidak valid. Wajib 'VALID' atau 'REJECT'.");
        }

        if ($verification->isResolved()) {
            throw new \DomainException("Sesi verifikasi misi ini sudah selesai dengan status: {$verification->status}");
        }

        return DB::transaction(function () use ($juror, $verification, $vote, $notes) {
            // 1. Temukan penugasan juri
            $assignment = MissionVerificationJuror::where('verification_id', $verification->id)
                ->where('juror_user_id', $juror->id)
                ->first();

            if (!$assignment) {
                throw new \DomainException("Anda tidak terdaftar sebagai salah satu dari 5 juri untuk misi ini.");
            }

            if ($assignment->vote !== 'PENDING') {
                throw new \DomainException("Anda sudah memberikan suara untuk misi ini sebelumnya.");
            }

            // 2. Potong pasak 50 Perak dari dompet juri
            $stakeAmount = $assignment->stake_amount_silver;
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $juror->id],
                ['silver_balance' => 0, 'gold_balance' => 0]
            );

            if ($wallet->silver_balance < $stakeAmount) {
                throw new \DomainException("Saldo Koin Perak Anda tidak mencukupi untuk pasak juri ({$stakeAmount} Perak).");
            }

            $wallet->decrement('silver_balance', $stakeAmount);

            // 3. Catat vote juri
            $assignment->update([
                'vote' => $vote,
                'notes' => $notes,
                'voted_at' => now(),
            ]);

            // 4. Update akumulasi suara pada verifikasi
            if ($vote === 'VALID') {
                $verification->increment('valid_votes_count');
            } else {
                $verification->increment('reject_votes_count');
            }

            $verification->refresh();

            // 5. Pemeriksaan Honeypot Trap (Sistem Jebakan)
            $fellForHoneypot = false;
            if ($verification->is_honeypot) {
                if ($vote === 'VALID') {
                    // Juri ceroboh/malas menyetujui data jebakan palsu!
                    $fellForHoneypot = true;
                    $this->triggerHoneypotSlash($juror, $verification, $assignment);
                } else {
                    // Juri teliti dan menolak jebakan: Hadiahi bonus XP
                    $juror->increment('experience_points', 50);
                }
            }

            // 6. Evaluasi Konsensus (Apakah sudah tercapai 3/5 suara)
            $consensusReached = $this->evaluateConsensus($verification);

            return [
                'success' => true,
                'message' => $fellForHoneypot 
                    ? 'PERINGATAN: Misi ini adalah JEBAKAN HONEYPOT! Suara VALID Anda mengakibatkan penalti reputasi dan skorsing juri.'
                    : 'Suara Anda berhasil dicatat dengan pasak 50 Koin Perak.',
                'vote' => $vote,
                'fell_for_honeypot' => $fellForHoneypot,
                'consensus_reached' => $consensusReached,
                'new_status' => $verification->fresh()->status,
                'current_valid' => $verification->valid_votes_count,
                'current_reject' => $verification->reject_votes_count,
            ];
        });
    }

    /**
     * Evaluasi tercapainya konsensus mayoritas (≥ 3 suara)
     */
    public function evaluateConsensus(MissionVerification $verification): bool
    {
        $verification->refresh();

        if ($verification->isResolved()) {
            return true;
        }

        // Syarat menang: minimal 3 suara sependapat
        if ($verification->valid_votes_count >= 3) {
            $this->finalizeConsensus($verification, 'CONSENSUS_APPROVED');
            return true;
        } elseif ($verification->reject_votes_count >= 3) {
            $this->finalizeConsensus($verification, 'CONSENSUS_REJECTED');
            return true;
        }

        return false;
    }

    /**
     * Finalisasi konsensus, pencairan reward misi, dan pembagian dividen pasak juri
     */
    protected function finalizeConsensus(MissionVerification $verification, string $finalStatus): void
    {
        $verification->update([
            'status' => $finalStatus,
            'consensus_reached_at' => now(),
        ]);

        $winningVote = ($finalStatus === 'CONSENSUS_APPROVED') ? 'VALID' : 'REJECT';

        // 1. Jika Lulus: Distribusikan hadiah misi ke pembuat bukti
        if ($finalStatus === 'CONSENSUS_APPROVED') {
            $submitterWallet = Wallet::firstOrCreate(
                ['user_id' => $verification->submitter_user_id],
                ['silver_balance' => 0, 'gold_balance' => 0]
            );

            if ($verification->reward_gold > 0) {
                $submitterWallet->increment('gold_balance', $verification->reward_gold);
            }
            if ($verification->reward_silver > 0) {
                $submitterWallet->increment('silver_balance', $verification->reward_silver);
            }
            $verification->submitter->increment('experience_points', 100);
        }

        // 2. Evaluasi seluruh juri yang telah memberikan suara
        $jurors = MissionVerificationJuror::where('verification_id', $verification->id)->get();

        foreach ($jurors as $assignment) {
            // Abaikan juri yang belum voting atau yang sudah di-slash honeypot
            if ($assignment->vote === 'PENDING' || $assignment->is_slashed) {
                continue;
            }

            $jurorUser = $assignment->juror;
            $jurorWallet = Wallet::firstOrCreate(
                ['user_id' => $jurorUser->id],
                ['silver_balance' => 0, 'gold_balance' => 0]
            );

            if ($assignment->vote === $winningVote) {
                // Juri Pemenang Konsensus:
                // Pasak 50 Perak dikembalikan + Dividen Insentif (+25 Perak & +15 XP)
                $refundStake = $assignment->stake_amount_silver;
                $incentive = 25;
                $totalReturn = $refundStake + $incentive;

                $jurorWallet->increment('silver_balance', $totalReturn);
                $jurorUser->increment('experience_points', 15);

                $assignment->update([
                    'reward_amount_silver' => $incentive,
                ]);
            } else {
                // Juri Minoritas / Kalah Konsensus:
                // Pasak 50 Perak hangus (slashed)
                $assignment->update([
                    'is_slashed' => true,
                    'reward_amount_silver' => 0,
                ]);
            }
        }
    }

    /**
     * Sanksi keras Honeypot Slashing (-100 reputasi & Banned 7 hari)
     */
    public function triggerHoneypotSlash(User $juror, MissionVerification $verification, MissionVerificationJuror $assignment): void
    {
        // 1. Eksekusi penalti -100 poin reputasi via ReputationService
        $this->reputationService->applySlashingPenalty(
            $juror->id,
            'HONEYPOT_FRAUD_VALIDATION',
            100
        );

        // 2. Pasak juri hangus seketika
        $assignment->update([
            'is_slashed' => true,
            'reward_amount_silver' => 0,
        ]);

        // 3. Skorsing hak juri selama 7 hari
        $juror->update([
            'jury_banned_until' => now()->addDays(7),
        ]);

        Log::alert("JURI KENA SANKSI HONEYPOT: User {$juror->name} ({$juror->id}) menyetujui misi palsu {$verification->id}. Reputasi dipotong 100 poin & dibekukan 7 hari.");
    }

    /**
     * Generator Misi Jebakan (Honeypot Trap) Otomatis
     */
    public function createHoneypot(string $title, string $fakeProofDescription, ?string $fakeImageUrl = null): MissionVerification
    {
        // Cari user admin / root sebagai submitter honeypot sistem
        $systemUser = User::where('role', 'ADMIN')->first() ?? User::first();

        return MissionVerification::create([
            'submitter_user_id' => $systemUser->id,
            'mission_title' => '[HONEYPOT] ' . $title,
            'proof_image_url' => $fakeImageUrl,
            'proof_description' => $fakeProofDescription,
            'reward_gold' => 0,
            'reward_silver' => 0,
            'is_honeypot' => true,
            'honeypot_correct_verdict' => 'REJECT',
            'status' => 'IN_VOTING',
            'valid_votes_count' => 0,
            'reject_votes_count' => 0,
            'total_jurors_count' => 5,
        ]);
    }

    /**
     * Ambil daftar tugas juri yang masih menunggu suara pengguna ini
     */
    public function getPendingTasksForUser(User $user)
    {
        return MissionVerificationJuror::with(['verification.submitter', 'verification.quest'])
            ->where('juror_user_id', $user->id)
            ->where('vote', 'PENDING')
            ->whereHas('verification', function ($q) {
                $q->where('status', 'IN_VOTING');
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Ambil seluruh riwayat penugasan juri pengguna ini
     */
    public function getAllJurorHistoryForUser(User $user)
    {
        return MissionVerificationJuror::with(['verification'])
            ->where('juror_user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Ambil histori pengajuan misi milik pengguna ini
     */
    public function getMySubmissions(User $user)
    {
        return MissionVerification::with(['jurors.juror'])
            ->where('submitter_user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
