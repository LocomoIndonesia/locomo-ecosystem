<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Memfasilitasi pergantian peran/user cepat untuk demonstrasi & pengujian UI
     */
    public function switchUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();
        Auth::login($user);

        return redirect()->back()->with('success', "Beralih ke akun {$user->name}.");
    }
}
