<?php

namespace App\Http\Controllers;

use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class QuestController extends Controller
{
    /**
     * Stake utility coins (Silver) to begin an expedition.
     */
    public function stake(Request $request)
    {
        $request->validate([
            'quest_id' => 'required|uuid',
        ]);

        try {
            $result = WalletService::stakeQuest(Auth::id(), $request->quest_id);
            
            return response()->json([
                'success' => true,
                'message' => 'Quest berhasil distake (Ekspedisi Dimulai)',
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Resolve quest success/failure (Mock Admin action).
     */
    public function resolve(Request $request)
    {
        // Protect with admin key check
        $adminKey = $request->header('x-admin-key');
        if ($adminKey !== 'piticoin_admin_secret') {
            return response()->json([
                'success' => false,
                'error' => 'Akses ditolak: Membutuhkan otorisasi Admin',
            ], 403);
        }

        $request->validate([
            'escrow_id' => 'required|uuid',
            'is_success' => 'required|boolean',
        ]);

        try {
            if ($request->is_success) {
                $result = WalletService::resolveQuestSuccess($request->escrow_id);
            } else {
                $result = WalletService::resolveQuestFailed($request->escrow_id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Resolusi quest berhasil disinkronkan',
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
