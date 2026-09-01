<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class ExperienceService
{
    /**
     * Add experience points (XP) to a user, log the transaction, and handle potential level ups.
     *
     * @param string $userId
     * @param int $amount Amount of XP to add/remove
     * @param string $source The source category (e.g. QUEST_RESOLVED, ONBOARDING, MINI_GAME)
     * @param string $description Detailed message about the source
     * @return array Updated level and experience points
     */
    public static function addExperience(string $userId, int $amount, string $source, string $description)
    {
        return DB::transaction(function () use ($userId, $amount, $source, $description) {
            $user = User::findOrFail($userId);

            // 1. Insert experience log entry
            DB::table('experience_logs')->insert([
                'id' => DB::raw('gen_random_uuid()'),
                'user_id' => $userId,
                'xp_amount' => $amount,
                'source' => $source,
                'description' => $description,
                'created_at' => now(),
            ]);

            // 2. Calculate new XP points
            $currentXp = $user->experience_points + $amount;
            if ($currentXp < 0) {
                $currentXp = 0; // prevent negative total XP
            }

            $currentLevel = $user->level;

            // 3. Check for level ups using formula: XP threshold = level * 1000
            // Example: Level 1 needs 1000 XP to level up. Level 2 needs 2000 XP, etc.
            while (true) {
                $threshold = $currentLevel * 1000;
                if ($currentXp >= $threshold) {
                    $currentXp -= $threshold;
                    $currentLevel++;
                } else {
                    break;
                }
            }

            // 4. Update user record
            $user->update([
                'level' => $currentLevel,
                'experience_points' => $currentXp
            ]);

            return [
                'level' => $currentLevel,
                'experience_points' => $currentXp
            ];
        });
    }
}
