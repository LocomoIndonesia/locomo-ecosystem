<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class OwnerAuthController extends Controller
{
    /**
     * Tampilkan form registrasi owner.
     */
    public function showRegister()
    {
        if (Auth::check() && Auth::user()->isOwner()) {
            return redirect()->route('owner.dashboard');
        }
        return view('auth.owner-register');
    }

    /**
     * Proses pendaftaran tenant & owner baru.
     */
    public function register(Request $request)
    {
        $isGoogle = session()->has('google_register');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],
            'business_name' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:tenants,domain'],
            'business_type' => ['required', 'string', 'max:255'],
            'business_address' => ['required', 'string'],
            'business_description' => ['nullable', 'string', 'max:1000'],
            'package' => ['required', 'string', Rule::in(['Basic', 'Bisnis', 'Premium VIP'])],
        ];

        if (!$isGoogle) {
            $rules['password'] = ['required', 'string', 'min:6'];
        }

        $request->validate($rules);

        // 1. Buat Tenant baru
        $tenant = Tenant::create([
            'name' => $request->business_name,
            'domain' => $request->domain,
            'settings' => [
                'package' => $request->package,
                'tables_count' => 15,
                'tax_rate' => 10,
                'service_charge' => 5,
                'business_type' => $request->business_type,
                'business_address' => $request->business_address,
                'business_description' => $request->business_description,
            ]
        ]);

        // 2. Buat User Owner (terikat ke tenant baru)
        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $isGoogle ? null : Hash::make($request->password),
            'google_id' => $isGoogle ? session('google_register.google_id') : null,
            'avatar' => $isGoogle ? session('google_register.avatar') : null,
            'role' => 'owner',
            'pin' => strval(rand(1000, 9999)), // PIN acak 4 digit
        ]);

        if ($isGoogle) {
            session()->forget('google_register');
        }

        // 3. Login otomatis
        Auth::login($user);

        return redirect()->route('owner.dashboard')->with('success', 'Pendaftaran berhasil!');
    }

    /**
     * Tampilkan form login owner.
     */
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->isOwner()) {
            return redirect()->route('owner.dashboard');
        }
        return view('auth.owner-login');
    }

    /**
     * Proses login owner.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            $user = Auth::user();
            if ($user->isOwner()) {
                $request->session()->regenerate();
                return redirect()->intended(route('owner.dashboard'));
            }
            
            // Jika bukan owner, logout
            Auth::logout();
            return back()->withErrors([
                'email' => 'Akses ditolak. Rute ini hanya untuk pemilik bisnis (Owner).',
            ]);
        }

        return back()->withErrors([
            'email' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        ])->onlyInput('email');
    }

    /**
     * Proses logout owner.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('owner.login');
    }
}
