<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\AgentMission;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnimartWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Verify Webhook Signature
        $signature = $request->header('X-Unimart-Signature');
        $secret = env('WEBHOOK_SECRET', 'piticoin_local_secret_key');
        
        $computedSignature = hash_hmac('sha256', $request->getContent(), $secret);

        if (!$signature || !hash_equals($computedSignature, $signature)) {
            Log::warning('Unauthorized Unimart Webhook request blocked.', [
                'provided_signature' => $signature
            ]);
            return response()->json(['message' => 'Unauthorized signature verification failed.'], 401);
        }

        $event = $request->input('event');
        $email = $request->input('owner_email');
        $businessName = $request->input('business_name');

        if (!$event || !$email) {
            return response()->json(['message' => 'Invalid webhook payload structure.'], 400);
        }

        return DB::transaction(function () use ($event, $email, $businessName, $request) {
            // Find active or completed mission for this business
            $mission = AgentMission::where('owner_email', $email)
                ->whereIn('status', ['active', 'completed'])
                ->latest()
                ->first();

            if (!$mission) {
                return response()->json(['message' => 'No active mission found for this merchant email.'], 404);
            }

            if ($event === 'lead.confirmed') {
                if ($mission->status !== 'active') {
                    return response()->json(['message' => 'Mission is already confirmed.'], 200);
                }

                $mission->update([
                    'status' => 'completed',
                    'confirmed_at' => now(),
                ]);

                Log::info("Unimart Lead Confirmed successfully.", ['mission_id' => $mission->id]);
                return response()->json(['message' => 'Lead confirmation registered successfully.'], 200);
            }

            if ($event === 'payment.verified') {
                if ($mission->status === 'released') {
                    return response()->json(['message' => 'Mission rewards already released.'], 200);
                }

                $subscriptionPrice = floatval($request->input('subscription_price', 0)); // Price in silver
                // 25% reward in silver equivalent, then convert to Gold (1 Gold = 100 Silver)
                $rewardSilver = $subscriptionPrice * 0.25;
                $rewardGold = max(1, intval($rewardSilver / 100)); // Minimum 1 Gold

                $mission->update([
                    'status' => 'released',
                    'reward_gold_amount' => $rewardGold,
                    'released_at' => now(),
                ]);

                // Refund staking silver and award gold to agent wallet
                $wallet = Wallet::where('user_id', $mission->user_id)->first();
                if ($wallet) {
                    // Staking returned + gold commission minted
                    $wallet->silver_balance += intval($mission->staking_amount);
                    $wallet->gold_balance += $rewardGold;
                    $wallet->save();
                }

                Log::info("Unimart Payment Verified. Staking and rewards released.", [
                    'mission_id' => $mission->id,
                    'refunded_silver' => $mission->staking_amount,
                    'awarded_gold' => $rewardGold
                ]);

                return response()->json(['message' => 'Payment processed and rewards released.'], 200);
            }

            return response()->json(['message' => 'Unknown event type.'], 400);
        });
    }
}
