<?php

namespace App\Services;

use App\Models\UserReputation;
use App\Models\ReputationLog;
use Illuminate\Support\Facades\DB;

class ReputationService
{
    /**
     * Initialize reputation for a new user with 420 Respect.
     */
    public function initializeUser($userId)
    {
        return UserReputation::firstOrCreate(
            ['user_id' => $userId],
            [
                'base_score' => 420,
                'total_score' => 420,
                'current_status' => 'DIKENAL',
                'is_suspended' => false,
            ]
        );
    }

    /**
     * Add respect (endorsement) from one user to another.
     * Prevents farming by applying diminishing returns if giving to the same person repeatedly.
     */
    public function addRespect($giverId, $receiverId)
    {
        return DB::transaction(function () use ($giverId, $receiverId) {
            // Count recent endorsements in the last 7 days from this giver to this receiver
            $recentCount = ReputationLog::where('giver_id', $giverId)
                ->where('receiver_id', $receiverId)
                ->where('action_type', 'ENDORSE')
                ->where('created_at', '>=', now()->subDays(7))
                ->count();

            // Diminishing returns calculation
            // If it's the first time, they get max network points (e.g., 5).
            // Second time: 5 / log2(2+1) = 3.1
            // Third time: 5 / log2(3+1) = 2.5
            $basePoint = 5;
            $diminishedPoint = max(0, (int) round($basePoint / log($recentCount + 2, 2)));

            if ($diminishedPoint > 0) {
                // Create log
                ReputationLog::create([
                    'giver_id' => $giverId,
                    'receiver_id' => $receiverId,
                    'action_type' => 'ENDORSE',
                    'point_change' => $diminishedPoint,
                    'description' => 'Received respect from a peer.',
                ]);

                // Update network score and total score
                $rep = UserReputation::firstOrCreate(['user_id' => $receiverId], ['base_score' => 420, 'total_score' => 420]);
                $rep->network_score += $diminishedPoint;
                $rep->save();

                $this->evaluateStatus($receiverId);
            }

            return $diminishedPoint;
        });
    }

    /**
     * Apply a drastic penalty (slashing) for violations.
     */
    public function applySlashingPenalty($userId, $violationType, $penaltyAmount = 100)
    {
        return DB::transaction(function () use ($userId, $violationType, $penaltyAmount) {
            ReputationLog::create([
                'giver_id' => null, // System
                'receiver_id' => $userId,
                'action_type' => 'SLASHING',
                'point_change' => -$penaltyAmount,
                'description' => "Penalty for violation: $violationType",
            ]);

            $rep = UserReputation::firstOrCreate(['user_id' => $userId], ['base_score' => 420, 'total_score' => 420]);
            // Reduce base score to simulate permanent penalty
            $rep->base_score -= $penaltyAmount;
            $rep->save();

            $this->evaluateStatus($userId);
            
            return $rep;
        });
    }

    /**
     * Re-calculate total score based on weights and update status.
     * Score = 420 + (W_net * 0.2) + (W_trx * 0.3) + (W_chk * 0.2) + (W_mis * 0.3)
     */
    public function evaluateStatus($userId)
    {
        $rep = UserReputation::firstOrCreate(['user_id' => $userId], ['base_score' => 420, 'total_score' => 420]);
        
        $total = $rep->base_score 
               + round($rep->network_score * 0.2) 
               + round($rep->transaction_score * 0.3) 
               + round($rep->checkin_score * 0.2) 
               + round($rep->mission_score * 0.3);

        $total = max(0, $total);
        $rep->total_score = $total;

        if ($total <= 0) {
            $rep->current_status = 'DIASINGKAN';
            $rep->is_suspended = true;
        } elseif ($total >= 1 && $total <= 419) {
            $rep->current_status = 'DIBENCI';
            $rep->is_suspended = false;
        } elseif ($total >= 420 && $total <= 599) {
            $rep->current_status = 'DIKENAL';
            $rep->is_suspended = false;
        } elseif ($total >= 600 && $total <= 799) {
            $rep->current_status = 'DISUKAI';
            $rep->is_suspended = false;
        } elseif ($total >= 800 && $total <= 999) {
            $rep->current_status = 'DIHORMATI';
            $rep->is_suspended = false;
        } else {
            $rep->current_status = 'DICINTAI';
            $rep->is_suspended = false;
        }

        $rep->save();
        return $rep;
    }
}
