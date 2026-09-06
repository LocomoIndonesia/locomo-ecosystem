<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GatewayService
{
    /**
     * Kirim event ke locomo.gateway WebSocket server.
     */
    public static function emit(string $channel, string $event, array $data): bool
    {
        try {
            $internalUrl = config('services.gateway.internal_url', 'http://gateway:3000');
            $url = rtrim($internalUrl, '/') . '/emit';

            Http::timeout(2)->post($url, [
                'channel' => $channel,
                'event' => $event,
                'data' => $data,
            ]);
            return true;
        } catch (\Throwable $e) {
            Log::warning("Gateway emit error [{$channel}/{$event}]: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Dapatkan URL client-side untuk koneksi Socket.io di browser.
     */
    public static function getClientSocketUrl(): string
    {
        $configured = config('services.gateway.client_url');
        if (!empty($configured)) {
            return $configured;
        }

        $host = request()->getHost();
        $isLocal = str_ends_with($host, '.localhost') || $host === 'localhost' || $host === '127.0.0.1';

        if ($isLocal) {
            return 'http://gateway.locomo.id.localhost';
        }

        $scheme = request()->isSecure() ? 'https' : 'http';
        $domain = env('APP_BASE_DOMAIN', 'unimart.group');
        return "{$scheme}://gateway.{$domain}";
    }
}
