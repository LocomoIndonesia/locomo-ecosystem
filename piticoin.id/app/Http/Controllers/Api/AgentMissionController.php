<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\AgentMission;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AgentMissionController extends Controller
{
    public function getActiveMission(Request $request)
    {
        $userId = Auth::id();
        $mission = AgentMission::where('user_id', $userId)
            ->whereIn('status', ['active', 'completed', 'released'])
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'mission' => $mission
        ]);
    }

    public function takeMission(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Unauthenticated.'], 401);
        }

        // 1. Check license level (needs Petualang or Saudagar, meaning license value >= 100)
        $allowedLicenses = ['PETUALANG', 'SAUDAGAR'];
        if (!in_array($user->license_type, $allowedLicenses)) {
            return response()->json([
                'success' => false,
                'error' => 'Akses Terkunci: Misi ini membutuhkan lisensi minimal 100 (Petualang / Saudagar). Lisensi Anda saat ini: ' . ($user->license_type ?: 'NONE')
            ], 403);
        }

        // 2. Check if user already has an active or completed (unreleased) mission
        $existingMission = AgentMission::where('user_id', $user->id)
            ->whereIn('status', ['active', 'completed'])
            ->first();

        if ($existingMission) {
            return response()->json([
                'success' => false,
                'error' => 'Anda sudah memiliki misi aktif atau tertunda saat ini.'
            ], 400);
        }

        // 3. Staking validation
        $wallet = Wallet::where('user_id', $user->id)->first();
        if (!$wallet || $wallet->silver_balance < 2500) {
            return response()->json([
                'success' => false,
                'error' => 'Saldo perak Anda tidak cukup untuk staking jaminan misi. Butuh 2.500 Perak.'
            ], 400);
        }

        return DB::transaction(function () use ($user, $wallet) {
            // Deduct staking perak
            $wallet->decrement('silver_balance', 2500);

            // Create Agent Mission
            $mission = AgentMission::create([
                'user_id' => $user->id,
                'mission_type' => 'unimart_registration',
                'status' => 'active',
                'staking_amount' => 2500,
                'business_name' => 'Pending Registration',
                'owner_email' => 'pending-' . Str::random(5) . '@unimart.group',
                'owner_phone' => '000000',
                'staking_locked_at' => now(),
                'expires_at' => now()->addHours(24),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Misi berhasil diambil. Staking 2.500 Perak dikunci selama 24 jam.',
                'mission' => $mission
            ]);
        });
    }

    public function submitLead(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|max:255',
            'owner_phone' => 'required|string|max:20',
            'business_type' => 'required|string',
        ]);

        $user = Auth::user();
        $mission = AgentMission::where('user_id', $user->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (!$mission) {
            return response()->json([
                'success' => false,
                'error' => 'Tidak ada misi aktif yang sedang berjalan. Ambil misi terlebih dahulu.'
            ], 400);
        }

        if (now()->gt($mission->expires_at)) {
            $mission->update(['status' => 'expired']);
            return response()->json([
                'success' => false,
                'error' => 'Misi Anda telah kedaluwarsa (melebihi batas waktu 24 jam).'
            ], 400);
        }

        // Send Lead to Unimart API
        $unimartUrl = env('UNIMART_API_URL', 'http://unimart.group.localhost/api/v1') . '/unimart/register-lead';
        
        try {
            $response = Http::post($unimartUrl, [
                'business_name' => $request->business_name,
                'owner_name' => $request->owner_name,
                'owner_email' => $request->owner_email,
                'owner_phone' => $request->owner_phone,
                'business_type' => $request->business_type,
                'agent_id' => $user->id,
            ]);

            if ($response->failed()) {
                Log::error('Unimart API lead registration failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return response()->json([
                    'success' => false,
                    'error' => 'Gagal terhubung dengan server Unimart untuk pendaftaran.'
                ], 500);
            }

            // Update PitiCoin Mission details
            $mission->update([
                'business_name' => $request->business_name,
                'owner_email' => $request->owner_email,
                'owner_phone' => $request->owner_phone,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Detail bisnis berhasil dikirim ke Unimart. Menunggu konfirmasi pemilik usaha.',
                'mission' => $mission
            ]);

        } catch (\Exception $e) {
            Log::error('Exception during Unimart lead registration API call', ['exception' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'Server Unimart offline atau tidak dapat dijangkau.'
            ], 500);
        }
    }
}
