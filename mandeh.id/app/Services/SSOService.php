<?php

namespace App\Services;

class SSOService
{
    /**
     * Base64Url encoding helper.
     */
    public function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    /**
     * Base64Url decoding helper.
     */
    public function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }

    /**
     * Generate a signed RS256 JWT token.
     */
    public function generateToken(array $payload, int $expiration = 3600): string
    {
        $privateKeyPath = storage_path('keys/private.key');
        if (!file_exists($privateKeyPath)) {
            throw new \Exception('Private key not found.');
        }

        $privateKey = file_get_contents($privateKeyPath);

        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
            'kid' => 'mandeh-key-1',
        ];

        $now = time();
        $payload['iat'] = $now;
        $payload['exp'] = $now + $expiration;
        $payload['nbf'] = $now;

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));

        $dataToSign = $headerEncoded . '.' . $payloadEncoded;
        $signature = '';

        if (!openssl_sign($dataToSign, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new \Exception('Signing failed.');
        }

        return $dataToSign . '.' . $this->base64UrlEncode($signature);
    }

    /**
     * Verify and decode an RS256 JWT token.
     */
    public function verifyToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;

        $dataToVerify = $headerEncoded . '.' . $payloadEncoded;
        $signature = $this->base64UrlDecode($signatureEncoded);

        $publicKeyPath = storage_path('keys/public.key');
        if (!file_exists($publicKeyPath)) {
            return null;
        }

        $publicKey = file_get_contents($publicKeyPath);

        $res = openssl_verify($dataToVerify, $signature, $publicKey, OPENSSL_ALGO_SHA256);
        if ($res !== 1) {
            return null;
        }

        $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);
        if (!$payload) {
            return null;
        }

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    /**
     * Generate a stateless authorization code (valid for 5 minutes).
     */
    public function generateAuthCode(string $userId, string $clientId, string $redirectUri, ?string $nonce = null): string
    {
        $payload = [
            'type' => 'auth_code',
            'sub' => $userId,
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'nonce' => $nonce,
        ];

        return $this->generateToken($payload, 300); // 5 minutes expiration
    }

    /**
     * Verify a stateless authorization code.
     */
    public function verifyAuthCode(string $code): ?array
    {
        $payload = $this->verifyToken($code);
        if (!$payload || !isset($payload['type']) || $payload['type'] !== 'auth_code') {
            return null;
        }
        return $payload;
    }

    /**
     * Get the JSON Web Key Set (JWKS) format of the public key.
     */
    public function getJWKS(): array
    {
        $publicKeyPath = storage_path('keys/public.key');
        if (!file_exists($publicKeyPath)) {
            throw new \Exception('Public key not found.');
        }

        $pubKeyPem = file_get_contents($publicKeyPath);
        $res = openssl_pkey_get_public($pubKeyPem);
        if (!$res) {
            throw new \Exception('Invalid public key.');
        }

        $details = openssl_pkey_get_details($res);
        if (!$details || !isset($details['rsa'])) {
            throw new \Exception('Not an RSA public key.');
        }

        $n = $this->base64UrlEncode($details['rsa']['n']);
        $e = $this->base64UrlEncode($details['rsa']['e']);

        return [
            'keys' => [
                [
                    'kty' => 'RSA',
                    'use' => 'sig',
                    'alg' => 'RS256',
                    'kid' => 'mandeh-key-1',
                    'n' => $n,
                    'e' => $e,
                ]
            ]
        ];
    }

    /**
     * Get the OpenID Connect Discovery configuration.
     */
    public function getDiscovery(): array
    {
        $issuer = url('/');
        return [
            'issuer' => $issuer,
            'authorization_endpoint' => $issuer . '/oauth/authorize',
            'token_endpoint' => $issuer . '/oauth/token',
            'userinfo_endpoint' => $issuer . '/oauth/userinfo',
            'jwks_uri' => $issuer . '/oauth/jwks',
            'response_types_supported' => ['code', 'token', 'id_token'],
            'subject_types_supported' => ['public'],
            'id_token_signing_alg_values_supported' => ['RS256'],
            'scopes_supported' => ['openid', 'profile', 'email'],
            'token_endpoint_auth_methods_supported' => ['client_secret_post', 'client_secret_basic'],
            'claims_supported' => ['iss', 'sub', 'aud', 'exp', 'iat', 'name', 'email', 'email_verified'],
        ];
    }
}
