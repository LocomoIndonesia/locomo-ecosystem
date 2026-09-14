<?php

namespace App\Http\Controllers;

use App\Services\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebPushController extends Controller
{
    protected WebPushService $webPushService;

    public function __construct(WebPushService $webPushService)
    {
        $this->webPushService = $webPushService;
    }

    /**
     * Dapatkan Public Key VAPID untuk frontend
     */
    public function getPublicKey(): JsonResponse
    {
        return response()->json([
            'publicKey' => $this->webPushService->getPublicKey(),
        ]);
    }

    /**
     * Daftarkan push subscription dari browser
     */
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
            'contentEncoding' => 'nullable|string',
        ]);

        try {
            $sub = $this->webPushService->subscribe(
                $request->user(),
                $request->all(),
                $request->userAgent()
            );

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi peramban berhasil diaktifkan.',
                'subscription_id' => $sub->id,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendaftarkan notifikasi: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Batalkan langganan push notification
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|string',
        ]);

        $this->webPushService->unsubscribe($request->endpoint);

        return response()->json([
            'success' => true,
            'message' => 'Langganan notifikasi berhasil dihentikan.',
        ]);
    }

    /**
     * Kirim notifikasi uji coba ke perangkat pengguna saat ini
     */
    public function sendTest(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda wajib masuk untuk mengirim notifikasi uji coba.',
            ], 401);
        }

        $result = $this->webPushService->sendToUser(
            $user,
            '🔔 Ekspedisi PitiCoin.id',
            'Selamat! Notifikasi peramban terhubung sempurna ke stasiun ekspedisi Anda.',
            [
                'url' => route('dashboard', [], false),
                'type' => 'test_alert',
                'tag' => 'piticoin-test-' . time(),
            ]
        );

        if ($result['sent'] > 0) {
            return response()->json([
                'success' => true,
                'message' => "Notifikasi uji coba berhasil dikirim ke {$result['sent']} perangkat aktif.",
                'data' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tidak ada langganan notifikasi aktif yang ditemukan untuk akun Anda. Harap aktifkan izin notifikasi di peramban.',
            'data' => $result,
        ], 400);
    }
}
