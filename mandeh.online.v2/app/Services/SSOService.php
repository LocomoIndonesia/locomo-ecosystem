<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SSOService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $idUrl;
    protected string $publicIdUrl;
    protected string $redirectUri;

    public function __construct()
    {
        $this->clientId = config('services.mandeh_id.client_id');
        $this->clientSecret = config('services.mandeh_id.client_secret');
        $this->idUrl = config('services.mandeh_id.url');
        $this->publicIdUrl = config('services.mandeh_id.public_url');
        $this->redirectUri = route('sso.callback');
    }

    /**
     * Get redirect URL to Mandeh.ID authorize page
     */
    public function getAuthorizationUrl(string $state, string $nonce = ''): string
    {
        $query = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'state' => $state,
            'nonce' => $nonce,
            'scope' => 'openid profile email',
        ]);

        return $this->publicIdUrl . '/oauth/authorize?' . $query;
    }

    /**
     * Exchange auth code for tokens
     */
    public function exchangeCode(string $code): ?array
    {
        try {
            // Send request to token endpoint using local docker service name
            $response = Http::asForm()->post($this->idUrl . '/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code,
                'redirect_uri' => $this->redirectUri,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('SSO token exchange failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('SSO token exchange exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Retrieve user profile via userinfo endpoint using access token
     */
    public function getUserInfo(string $accessToken): ?array
    {
        try {
            $response = Http::withToken($accessToken)
                ->get($this->idUrl . '/oauth/userinfo');

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('SSO userinfo request failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('SSO userinfo request exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Helper to decode JWT payload without validation
     */
    public function decodeToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        $payload = str_replace(['-', '_'], ['+', '/'], $parts[1]);
        $decoded = base64_decode($payload);
        if (!$decoded) {
            return null;
        }

        return json_decode($decoded, true);
    }
}
