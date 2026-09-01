<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;

class GoogleAuthController extends Controller
{
    /**
     * Redirect ke Google Sign-In.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle callback dari Google.
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('owner.login')->withErrors([
                'email' => 'Gagal autentikasi dengan Google: ' . $e->getMessage()
            ]);
        }

        // Cari user berdasarkan google_id
        $user = User::where('google_id', $googleUser->getId())->first();

        if ($user) {
            Auth::login($user);
            return redirect()->route('owner.dashboard')->with('success', 'Login berhasil menggunakan Google!');
        }

        // Jika google_id belum ada, cari berdasarkan email (menghubungkan akun)
        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
            ]);

            Auth::login($user);
            return redirect()->route('owner.dashboard')->with('success', 'Akun Google Anda berhasil dihubungkan!');
        }

        // Skenario: User Baru (Progressive Registration)
        // Simpan data dari Google ke dalam Session
        session([
            'google_register' => [
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
            ]
        ]);

        return redirect()->route('owner.register')->with('info', 'Lengkapi detail bisnis Anda untuk menyelesaikan pendaftaran.');
    }
}
