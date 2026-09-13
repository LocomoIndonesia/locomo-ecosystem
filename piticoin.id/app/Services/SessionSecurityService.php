<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SessionSecurityService
{
    /**
     * Get all active sessions for the given user
     */
    public function getActiveSessions(User $user, Request $request): array
    {
        if (config('session.driver') !== 'database') {
            return [];
        }

        $currentSessionId = $request->session()->getId();

        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get();

        return $sessions->map(function ($session) use ($currentSessionId) {
            $agent = $this->parseUserAgent($session->user_agent ?? '');

            return [
                'id' => $session->id,
                'ip_address' => $session->ip_address,
                'is_current_device' => $session->id === $currentSessionId,
                'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                'last_active_timestamp' => $session->last_activity,
                'device_type' => $agent['device'],
                'platform' => $agent['platform'],
                'browser' => $agent['browser'],
            ];
        })->toArray();
    }

    /**
     * Revoke all other sessions for the user after validating password
     */
    public function revokeOtherSessions(User $user, string $password): bool
    {
        if (!Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Kata sandi yang Anda masukkan salah.'],
            ]);
        }

        // Laravel built-in to revoke other device sessions
        Auth::logoutOtherDevices($password);

        return true;
    }

    /**
     * Helper to parse simple device and browser information from User-Agent
     */
    private function parseUserAgent(string $userAgent): array
    {
        $platform = 'Unknown OS';
        $browser = 'Unknown Browser';
        $device = 'Desktop';

        // Platform detection
        if (preg_match('/windows|win32/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/android/i', $userAgent)) {
            $platform = 'Android';
            $device = 'Mobile';
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            $platform = 'iOS';
            $device = 'Mobile';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $platform = 'Linux';
        }

        // Browser detection
        if (preg_match('/chrome|crios/i', $userAgent) && !preg_match('/edg|opr/i', $userAgent)) {
            $browser = 'Google Chrome';
        } elseif (preg_match('/firefox|fxios/i', $userAgent)) {
            $browser = 'Mozilla Firefox';
        } elseif (preg_match('/safari/i', $userAgent) && !preg_match('/chrome|crios/i', $userAgent)) {
            $browser = 'Apple Safari';
        } elseif (preg_match('/edg/i', $userAgent)) {
            $browser = 'Microsoft Edge';
        } elseif (preg_match('/opera|opr/i', $userAgent)) {
            $browser = 'Opera';
        }

        return [
            'platform' => $platform,
            'browser' => $browser,
            'device' => $device,
        ];
    }
}
