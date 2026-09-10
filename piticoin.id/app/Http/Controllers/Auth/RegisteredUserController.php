<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:255|unique:'.User::class,
            'password' => [
                'required',
                'confirmed',
                Rules\Password::min(8)
                    ->letters()
                    ->numbers(),
            ],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp.unique' => 'Nomor WhatsApp ini sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok dengan password.',
            'password.min' => 'Password minimal harus 8 karakter.',
        ]);

        $uplineId = null;
        $grandUplineId = null;

        // Process referral code (whatsapp number of the upline)
        if ($request->filled('referral_code') && $request->referral_code !== $request->whatsapp) {
            $uplineUser = User::where('whatsapp', $request->referral_code)->first();
            if ($uplineUser) {
                $uplineId = $uplineUser->id;
                $grandUplineId = $uplineUser->upline_id;
            }
        }

        // Run user & wallet creation in a transaction
        $user = DB::transaction(function () use ($request, $uplineId, $grandUplineId) {
            $user = User::create([
                'name' => $request->name,
                'whatsapp' => $request->whatsapp,
                'role' => 'GUEST',
                'upline_id' => $uplineId,
                'grand_upline_id' => $grandUplineId,
                'password' => Hash::make($request->password),
            ]);

            Wallet::create([
                'user_id' => $user->id,
                'silver_balance' => 0,
                'gold_balance' => 0,
            ]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('onboarding.awakening', absolute: false));
    }
}
