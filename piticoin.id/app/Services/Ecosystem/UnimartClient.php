<?php

namespace App\Services\Ecosystem;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UnimartClient
{
    /**
     * Daftarkan toko baru ke Unimart.group via External Merchant Registration API.
     */
    public function registerStore(array $payload): array
    {
        $apiUrl = config('ecosystem.services.unimart.api_url');
        $baseDomain = config('ecosystem.services.unimart.base_domain');
        $secret = config('ecosystem.secret');

        Log::info('Dispatching merchant registration to Unimart API', [
            'url' => $apiUrl,
            'business_name' => $payload['business_name'] ?? null,
            'license_code' => $payload['license_code'] ?? null,
        ]);

        try {
            // Ketika di local Docker, kita menyertakan header Host agar Nginx me-route ke virtual host unimart.group.localhost
            $request = Http::timeout(10)->withHeaders([
                'X-Locomo-Secret' => $secret,
                'Host' => $baseDomain,
                'Accept' => 'application/json',
            ]);

            $response = $request->post($apiUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Unimart merchant registered successfully', [
                    'tenant_id' => $data['tenant']['id'] ?? null,
                    'store_url' => $data['tenant']['store_url'] ?? null,
                ]);

                return [
                    'success' => true,
                    'data' => $data,
                    'store_url' => $data['tenant']['store_url'] ?? "http://{$baseDomain}/s/" . ($payload['domain'] ?? ''),
                    'menu_url' => $data['tenant']['menu_url'] ?? null,
                    'login_url' => $data['owner']['login_url'] ?? "http://{$baseDomain}/owner/login",
                    'initial_password' => $data['owner']['initial_password'] ?? null,
                ];
            }

            Log::error('Unimart registration API failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => $response->json('message') ?: 'Gagal mendaftarkan toko ke server Unimart (' . $response->status() . ').',
                'store_url' => "http://{$baseDomain}/s/" . ($payload['domain'] ?? ''),
            ];
        } catch (\Throwable $e) {
            Log::error('Exception connecting to Unimart registration API: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Koneksi ke server Unimart terputus: ' . $e->getMessage(),
                'store_url' => "http://{$baseDomain}/s/" . ($payload['domain'] ?? ''),
            ];
        }
    }
}
