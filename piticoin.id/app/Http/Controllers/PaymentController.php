<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Exception;

class PaymentController extends Controller
{
    /**
     * Webhook listener simulating Payment Gateway notifying 100k IDR upgrade payment.
     */
    public function webhook(Request $request)
    {
        $userId = $request->userId;
        $whatsapp = $request->whatsapp;

        try {
            if (!$userId && $whatsapp) {
                $user = User::where('whatsapp', $whatsapp)->first();
                if (!$user) {
                    return response()->json(['success' => false, 'error' => 'User tidak ditemukan'], 404);
                }
                $userId = $user->id;
            }

            if (!$userId) {
                return response()->json(['success' => false, 'error' => 'userId atau whatsapp diperlukan'], 400);
            }

            $result = WalletService::purchaseStarterKit($userId);

            return response()->json([
                'success' => true,
                'message' => 'Upgrade merchant berhasil diproses melalui Webhook',
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
