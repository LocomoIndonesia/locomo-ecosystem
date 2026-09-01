<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Services\SSOService;

class SSOTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Run database seeders to populate clients and test user
        $this->seed();
    }

    public function test_openid_discovery()
    {
        $response = $this->get('/.well-known/openid-configuration');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'issuer',
            'authorization_endpoint',
            'token_endpoint',
            'jwks_uri',
        ]);
    }

    public function test_jwks_endpoint()
    {
        $response = $this->get('/oauth/jwks');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'keys' => [
                '*' => ['kty', 'use', 'alg', 'kid', 'n', 'e']
            ]
        ]);
    }

    public function test_authorize_form_requires_client_id_and_redirect_uri()
    {
        $response = $this->get('/oauth/authorize');
        $response->assertStatus(400);
    }

    public function test_authorize_form_validates_client_and_redirect_uri()
    {
        // Invalid client_id
        $response = $this->get('/oauth/authorize?client_id=invalid-client&redirect_uri=http://locomo-web.localhost/callback');
        $response->assertStatus(400);

        // Valid client_id, invalid redirect_uri
        $response = $this->get('/oauth/authorize?client_id=locomo-web-client&redirect_uri=http://invalid.localhost/callback');
        $response->assertStatus(400);

        // Valid request, prompts login view
        $response = $this->get('/oauth/authorize?client_id=locomo-web-client&redirect_uri=http://locomo-web.localhost/callback&response_type=code');
        $response->assertStatus(200);
        $response->assertViewIs('sso.login');
    }

    public function test_login_flow_and_stateless_code_exchange()
    {
        // Prime OIDC session variables via authorization endpoint first
        $this->get('/oauth/authorize?client_id=locomo-web-client&redirect_uri=http://locomo-web.localhost/callback&response_type=code&state=xyz123&nonce=noncy');

        // Send login POST request with valid credentials
        $response = $this->post('/oauth/authorize', [
            'email' => 'budi@mandeh.id',
            'password' => 'password',
        ]);

        // Should redirect back to client redirect_uri with a stateless code query parameter
        $response->assertRedirect();
        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('http://locomo-web.localhost/callback', $redirectUrl);
        $this->assertStringContainsString('code=', $redirectUrl);

        // Parse code from redirect url
        parse_str(parse_url($redirectUrl, PHP_URL_QUERY), $query);
        $code = $query['code'];
        $this->assertNotEmpty($code);

        // Verify that the code works by requesting token exchange
        $tokenResponse = $this->postJson('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => 'locomo-web-client',
            'client_secret' => 'locomo-secret',
            'code' => $code,
            'redirect_uri' => 'http://locomo-web.localhost/callback',
        ]);

        $tokenResponse->assertStatus(200);
        $tokenResponse->assertJsonStructure([
            'access_token',
            'id_token',
            'token_type',
            'expires_in',
        ]);

        $accessToken = $tokenResponse->json('access_token');
        $idToken = $tokenResponse->json('id_token');

        // Verify userinfo endpoint using access token
        $userinfoResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->getJson('/oauth/userinfo');

        $userinfoResponse->assertStatus(200);
        $userinfoResponse->assertJson([
            'email' => 'budi@mandeh.id',
            'name' => 'Budi Pandeka',
        ]);
    }
}
