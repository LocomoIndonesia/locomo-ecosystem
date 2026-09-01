<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SSOService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SSOClientController extends Controller
{
    protected SSOService $ssoService;

    public function __construct(SSOService $ssoService)
    {
        $this->ssoService = $ssoService;
    }

    /**
     * Redirect to SSO login server
     */
    public function redirect(Request $request)
    {
        $state = Str::random(40);
        $nonce = Str::random(40);

        session([
            'sso_state' => $state,
            'sso_nonce' => $nonce,
        ]);

        return redirect($this->ssoService->getAuthorizationUrl($state, $nonce));
    }

    /**
     * Handle OAuth callback from SSO server
     */
    public function callback(Request $request)
    {
        $state = $request->query('state');
        $code = $request->query('code');

        $savedState = session('sso_state');
        session()->forget('sso_state');

        if (!$state || $state !== $savedState) {
            return redirect()->route('login')->withErrors(['sso' => 'Invalid state token.']);
        }

        if (!$code) {
            return redirect()->route('login')->withErrors(['sso' => 'Authorization code missing.']);
        }

        // Exchange code for tokens
        $tokens = $this->ssoService->exchangeCode($code);
        if (!$tokens || !isset($tokens['access_token'])) {
            return redirect()->route('login')->withErrors(['sso' => 'Failed to obtain tokens from SSO server.']);
        }

        // Fetch user info using access token
        $ssoUser = $this->ssoService->getUserInfo($tokens['access_token']);
        if (!$ssoUser || !isset($ssoUser['email'])) {
            return redirect()->route('login')->withErrors(['sso' => 'Failed to retrieve user profile.']);
        }

        // Find or create local user record
        $user = User::updateOrCreate([
            'email' => $ssoUser['email'],
        ], [
            'name' => $ssoUser['name'] ?? explode('@', $ssoUser['email'])[0],
            'password' => bcrypt(Str::random(24)), // Random password, not used for authentication
        ]);

        Auth::login($user);

        // Store token in session if needed for further api calls
        session([
            'sso_access_token' => $tokens['access_token'],
            'sso_id_token' => $tokens['id_token'] ?? null,
        ]);

        return redirect()->intended('/');
    }

    /**
     * Log user out locally
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
