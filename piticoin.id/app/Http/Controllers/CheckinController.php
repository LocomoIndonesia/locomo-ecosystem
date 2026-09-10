<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserCheckin;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckinController extends Controller
{
    /**
     * Get the time window details based on the current hour.
     */
    public static function getTimeWindowDetails(?Carbon $time = null): array
    {
        $time = $time ?? Carbon::now('Asia/Jakarta');
        $hour = $time->hour;

        if ($hour >= 5 && $hour < 12) {
            return [
                'window' => 'MORNING',
                'label' => 'Horizon Fajar (Pagi)',
                'time_range' => '05:00 - 11:59 WIB',
                'buff' => 'Buff Fajar: Bonus +10% XP untuk seluruh misi!',
                'buff_badge' => '+10% XP',
                'icon' => 'wb_twilight',
            ];
        } elseif ($hour >= 12 && $hour < 18) {
            return [
                'window' => 'AFTERNOON',
                'label' => 'Horizon Niaga (Siang/Sore)',
                'time_range' => '12:00 - 17:59 WIB',
                'buff' => 'Buff Niaga: Diskon Staking Perak 15% untuk misi lapangan!',
                'buff_badge' => '-15% STAKE',
                'icon' => 'wb_sunny',
            ];
        } else {
            return [
                'window' => 'EVENING',
                'label' => 'Horizon Senja (Malam)',
                'time_range' => '18:00 - 04:59 WIB',
                'buff' => 'Buff Senja: +20% Drop Rate item langka & Koin Emas di mini-game!',
                'buff_badge' => '+20% DROP',
                'icon' => 'dark_mode',
            ];
        }
    }

    /**
     * Get current user's check-in status.
     */
    public function getStatus(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        $timeDetails = self::getTimeWindowDetails($now);

        $todayCheckin = UserCheckin::where('user_id', $user->id)
            ->where('checkin_date', $today)
            ->first();

        // Calculate latest streak count
        $latestCheckin = UserCheckin::where('user_id', $user->id)
            ->orderBy('checkin_date', 'desc')
            ->first();

        $currentStreak = 0;
        if ($todayCheckin) {
            $currentStreak = $todayCheckin->streak_count;
        } elseif ($latestCheckin) {
            $yesterday = $now->copy()->subDay()->toDateString();
            if ($latestCheckin->checkin_date->toDateString() === $yesterday) {
                $currentStreak = $latestCheckin->streak_count;
            }
        }

        return response()->json([
            'success' => true,
            'has_checked_in' => (bool) $todayCheckin,
            'today_checkin' => $todayCheckin,
            'streak_count' => $currentStreak,
            'server_time' => $now->toIso8601String(),
            'time_window' => $timeDetails,
        ]);
    }

    /**
     * Perform daily check-in.
     */
    public function checkin(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();
        $timeStr = $now->toTimeString();
        $timeDetails = self::getTimeWindowDetails($now);

        return DB::transaction(function () use ($user, $now, $today, $timeStr, $timeDetails, $request) {
            // Check if already checked in today
            $existing = UserCheckin::where('user_id', $user->id)
                ->where('checkin_date', $today)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan aktivasi kompas hari ini!',
                    'checkin' => $existing,
                ], 400);
            }

            // Calculate streak
            $yesterday = $now->copy()->subDay()->toDateString();
            $yesterdayCheckin = UserCheckin::where('user_id', $user->id)
                ->where('checkin_date', $yesterday)
                ->first();

            $streak = $yesterdayCheckin ? $yesterdayCheckin->streak_count + 1 : 1;

            // Base rewards
            $silverReward = 250;
            $xpReward = 25;
            $goldBonus = 0;
            $bonusMessages = [];

            // Streak milestone bonus checks
            if ($streak === 3) {
                $silverReward += 500;
                $xpReward += 50;
                $bonusMessages[] = 'Bonus Streak 3 Hari: +500 Perak & +50 XP!';
            } elseif ($streak === 7) {
                $silverReward += 1500;
                $goldBonus += 1;
                $xpReward += 200;
                $bonusMessages[] = 'Bonus Streak 7 Hari: +1.500 Perak, +1 Koin Emas & +200 XP!';
            } elseif ($streak === 14) {
                $silverReward += 3500;
                $goldBonus += 3;
                $xpReward += 300;
                $bonusMessages[] = 'Bonus Streak 14 Hari: +3.500 Perak & +3 Koin Emas!';
            } elseif ($streak === 30) {
                $silverReward += 10000;
                $goldBonus += 10;
                $xpReward += 500;
                $bonusMessages[] = 'Puncak Kompas 30 Hari: +10.000 Perak, +10 Koin Emas & Gelar Saudagar Istiqomah!';
            }

            // Create Checkin record
            $checkin = UserCheckin::create([
                'user_id' => $user->id,
                'checkin_date' => $today,
                'checkin_time' => $timeStr,
                'time_window' => $timeDetails['window'],
                'streak_count' => $streak,
                'silver_reward_claimed' => $silverReward,
                'xp_reward_claimed' => $xpReward,
                'gold_bonus_claimed' => $goldBonus,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Credit Wallet
            $wallet = Wallet::firstOrCreate(['user_id' => $user->id], [
                'silver_balance' => 0,
                'gold_balance' => 0,
            ]);

            $wallet->silver_balance += $silverReward;
            if ($goldBonus > 0) {
                $wallet->gold_balance += $goldBonus;
            }
            $wallet->save();

            // Credit XP & Level progression
            $userModel = User::find($user->id);
            $userModel->experience_points += $xpReward;
            $xpThreshold = $userModel->level * 1000;
            if ($userModel->experience_points >= $xpThreshold) {
                $userModel->level += 1;
                $userModel->experience_points = $userModel->experience_points - $xpThreshold;
            }
            $userModel->save();

            return response()->json([
                'success' => true,
                'message' => 'Kompas Petualangan berhasil diaktifkan! Hadiah harian disuntikkan ke brankas Anda.',
                'bonus_messages' => $bonusMessages,
                'checkin' => $checkin,
                'streak_count' => $streak,
                'silver_reward' => $silverReward,
                'gold_bonus' => $goldBonus,
                'xp_reward' => $xpReward,
                'wallet' => $wallet,
                'user' => [
                    'level' => $userModel->level,
                    'experience_points' => $userModel->experience_points,
                ],
                'time_window' => $timeDetails,
            ]);
        });
    }
}
