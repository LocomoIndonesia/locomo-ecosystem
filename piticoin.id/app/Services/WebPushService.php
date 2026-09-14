<?php

namespace App\Services;

use App\Models\User;
use App\Models\WebPushSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    protected ?WebPush $webPush = null;

    public function __construct()
    {
        $this->initWebPush();
    }

    /**
     * Inisialisasi engine Minishlink WebPush dengan kredensial VAPID
     */
    protected function initWebPush(): void
    {
        $publicKey = config('webpush.vapid.public_key');
        $privateKey = config('webpush.vapid.private_key');
        $subject = config('webpush.vapid.subject', 'mailto:admin@piticoin.id');

        if (!empty($publicKey) && !empty($privateKey)) {
            $auth = [
                'VAPID' => [
                    'subject' => $subject,
                    'publicKey' => $publicKey,
                    'privateKey' => $privateKey,
                ],
            ];
            $options = config('webpush.options', [
                'TTL' => 86400,
                'urgency' => 'normal',
            ]);
            $this->webPush = new WebPush($auth, $options);
        }
    }

    /**
     * Dapatkan Public Key VAPID untuk dikirimkan ke frontend
     */
    public function getPublicKey(): ?string
    {
        return config('webpush.vapid.public_key');
    }

    /**
     * Daftarkan atau perbarui PushSubscription dari browser
     */
    public function subscribe(?User $user, array $subscriptionData, ?string $userAgent = null): WebPushSubscription
    {
        $endpoint = $subscriptionData['endpoint'] ?? null;
        $keys = $subscriptionData['keys'] ?? [];
        $publicKey = $keys['p256dh'] ?? ($subscriptionData['publicKey'] ?? '');
        $authToken = $keys['auth'] ?? ($subscriptionData['authToken'] ?? '');
        $contentEncoding = $subscriptionData['contentEncoding'] ?? 'aes128gcm';

        if (empty($endpoint) || empty($publicKey) || empty($authToken)) {
            throw new \InvalidArgumentException('Format subscription tidak valid. Endpoint, p256dh, dan auth token wajib diisi.');
        }

        return WebPushSubscription::updateOrCreate(
            ['endpoint' => $endpoint],
            [
                'user_id' => $user?->id,
                'public_key' => $publicKey,
                'auth_token' => $authToken,
                'content_encoding' => $contentEncoding,
                'user_agent' => $userAgent,
                'last_active_at' => now(),
            ]
        );
    }

    /**
     * Hapus langganan push untuk endpoint tertentu
     */
    public function unsubscribe(string $endpoint): bool
    {
        return (bool) WebPushSubscription::where('endpoint', $endpoint)->delete();
    }

    /**
     * Format payload JSON standar untuk notifikasi browser
     */
    protected function formatPayload(string $title, string $body, array $payloadData = []): string
    {
        $payload = [
            'title' => $title,
            'body' => $body,
            'icon' => $payloadData['icon'] ?? '/favicon.ico',
            'badge' => $payloadData['badge'] ?? '/favicon.ico',
            'url' => $payloadData['url'] ?? '/dashboard',
            'tag' => $payloadData['tag'] ?? 'piticoin-notif-' . time(),
            'timestamp' => now()->timestamp * 1000,
            'data' => array_merge([
                'url' => $payloadData['url'] ?? '/dashboard',
            ], $payloadData),
        ];

        return json_encode($payload);
    }

    /**
     * Kirim notifikasi ke satu subscription spesifik
     */
    public function sendToSubscription(WebPushSubscription $sub, string $title, string $body, array $payloadData = []): bool
    {
        if (!$this->webPush) {
            Log::warning('WebPushService: VAPID keys belum terkonfigurasi.');
            return false;
        }

        try {
            $webPushSub = $sub->toWebPushSubscription();
            $payload = $this->formatPayload($title, $body, $payloadData);

            $report = $this->webPush->sendOneNotification($webPushSub, $payload);

            if (!$report->isSuccess()) {
                Log::warning("WebPush gagal dikirim ke endpoint {$sub->endpoint}: " . $report->getReason());
                if ($report->isSubscriptionExpired()) {
                    $sub->delete();
                    Log::info("WebPushSubscription kadaluarsa/tidak valid telah dihapus: {$sub->endpoint}");
                }
                return false;
            }

            $sub->update(['last_active_at' => now()]);
            return true;
        } catch (\Throwable $e) {
            Log::error("WebPush exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim notifikasi ke seluruh subscription milik seorang User
     */
    public function sendToUser(User $user, string $title, string $body, array $payloadData = []): array
    {
        $subscriptions = WebPushSubscription::where('user_id', $user->id)->get();
        $successCount = 0;
        $failedCount = 0;

        foreach ($subscriptions as $sub) {
            if ($this->sendToSubscription($sub, $title, $body, $payloadData)) {
                $successCount++;
            } else {
                $failedCount++;
            }
        }

        return [
            'sent' => $successCount,
            'failed' => $failedCount,
            'total' => $subscriptions->count(),
        ];
    }

    /**
     * Broadcast notifikasi ke seluruh active subscriber
     */
    public function broadcast(string $title, string $body, array $payloadData = []): array
    {
        $subscriptions = WebPushSubscription::all();
        $successCount = 0;
        $failedCount = 0;

        foreach ($subscriptions as $sub) {
            if ($this->sendToSubscription($sub, $title, $body, $payloadData)) {
                $successCount++;
            } else {
                $failedCount++;
            }
        }

        return [
            'sent' => $successCount,
            'failed' => $failedCount,
            'total' => $subscriptions->count(),
        ];
    }
}
