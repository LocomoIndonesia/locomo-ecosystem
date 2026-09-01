<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SSOService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SSOController extends Controller
{
    protected $ssoService;

    public function __construct(SSOService $ssoService)
    {
        $this->ssoService = $ssoService;
    }

    public function discovery()
    {
        return response()->json($this->ssoService->getDiscovery(), 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function jwks()
    {
        return response()->json($this->ssoService->getJWKS());
    }

    public function authorizeForm(Request $request)
    {
        $clientId = $request->query('client_id');
        $redirectUri = $request->query('redirect_uri');
        $state = $request->query('state');
        $nonce = $request->query('nonce');

        if (!$clientId || !$redirectUri) {
            return response('Missing client_id or redirect_uri', 400);
        }

        $client = DB::table('sso_clients')->where('client_id', $clientId)->first();
        if (!$client) {
            return response('Invalid client_id', 400);
        }

        $allowedUris = array_map('trim', explode(',', $client->redirect_uris));
        if (!in_array($redirectUri, $allowedUris)) {
            return response('Invalid redirect_uri', 400);
        }

        session([
            'sso_client_id' => $clientId,
            'sso_redirect_uri' => $redirectUri,
            'sso_state' => $state,
            'sso_nonce' => $nonce,
        ]);

        if (Auth::check()) {
            return $this->redirectWithCode(Auth::user(), $clientId, $redirectUri, $nonce, $state);
        }

        return view('sso.login', [
            'client_name' => $client->name,
            'redirect_uri' => $redirectUri,
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $clientId = session('sso_client_id');
            $redirectUri = session('sso_redirect_uri');
            $state = session('sso_state');
            $nonce = session('sso_nonce');

            if ($clientId && $redirectUri) {
                session()->forget(['sso_client_id', 'sso_redirect_uri', 'sso_state', 'sso_nonce']);
                return $this->redirectWithCode(Auth::user(), $clientId, $redirectUri, $nonce, $state);
            }

            return redirect('/');
        }

        return back()->withErrors([
            'email' => 'Kredensial yang diberikan tidak cocok.',
        ])->onlyInput('email');
    }

    protected function redirectWithCode($user, $clientId, $redirectUri, $nonce, $state)
    {
        $code = $this->ssoService->generateAuthCode((string)$user->id, $clientId, $redirectUri, $nonce);
        
        $query = http_build_query([
            'code' => $code,
            'state' => $state,
        ]);

        return redirect($redirectUri . '?' . $query);
    }

    public function token(Request $request)
    {
        $clientId = $request->input('client_id') ?: $request->getUser();
        $clientSecret = $request->input('client_secret') ?: $request->getPassword();
        $code = $request->input('code');
        $redirectUri = $request->input('redirect_uri');
        $grantType = $request->input('grant_type', 'authorization_code');

        if ($grantType !== 'authorization_code') {
            return response()->json(['error' => 'unsupported_grant_type'], 400);
        }

        if (!$clientId || !$clientSecret || !$code) {
            return response()->json(['error' => 'invalid_request', 'message' => 'Missing required parameters.'], 400);
        }

        $client = DB::table('sso_clients')->where('client_id', $clientId)->first();
        if (!$client) {
            return response()->json(['error' => 'invalid_client'], 400);
        }

        if (!hash_equals($client->client_secret, hash('sha256', $clientSecret))) {
            return response()->json(['error' => 'invalid_client', 'message' => 'Client secret mismatch.'], 400);
        }

        $payload = $this->ssoService->verifyAuthCode($code);
        if (!$payload) {
            return response()->json(['error' => 'invalid_grant', 'message' => 'Authorization code has expired or is invalid.'], 400);
        }

        if ($payload['client_id'] !== $clientId) {
            return response()->json(['error' => 'invalid_grant', 'message' => 'Client ID mismatch.'], 400);
        }

        if ($redirectUri && $payload['redirect_uri'] !== $redirectUri) {
            return response()->json(['error' => 'invalid_grant', 'message' => 'Redirect URI mismatch.'], 400);
        }

        $user = User::find($payload['sub']);
        if (!$user) {
            return response()->json(['error' => 'invalid_grant', 'message' => 'User not found.'], 400);
        }

        $idTokenPayload = [
            'iss' => url('/'),
            'sub' => (string)$user->id,
            'aud' => $clientId,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified' => true,
        ];
        if (isset($payload['nonce'])) {
            $idTokenPayload['nonce'] = $payload['nonce'];
        }

        $idToken = $this->ssoService->generateToken($idTokenPayload, 3600);

        $accessTokenPayload = [
            'iss' => url('/'),
            'sub' => (string)$user->id,
            'aud' => $clientId,
            'scope' => 'openid profile email',
        ];
        $accessToken = $this->ssoService->generateToken($accessTokenPayload, 3600);

        return response()->json([
            'access_token' => $accessToken,
            'id_token' => $idToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'scope' => 'openid profile email',
        ]);
    }

    public function userinfo(Request $request)
    {
        $authHeader = $request->header('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response('Unauthorized', 401);
        }

        $token = substr($authHeader, 7);
        $payload = $this->ssoService->verifyToken($token);
        if (!$payload) {
            return response('Unauthorized', 401);
        }

        $user = User::find($payload['sub']);
        if (!$user) {
            return response('Unauthorized', 401);
        }

        return response()->json([
            'sub' => (string)$user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified' => true,
        ]);
    }
}
