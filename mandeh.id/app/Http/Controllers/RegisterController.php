<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisterController extends Controller
{
    public function showRegistrationForm(Request $request)
    {
        // Pass any OAuth parameters to the view so they can be included as hidden fields
        $oauthParams = $request->only(['client_id', 'redirect_uri', 'response_type', 'scope', 'state']);
        return view('sso.register', compact('oauthParams'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'user_type' => ['required', 'string', 'in:warga,wisatawan,bisnis,mitra,vanguard'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
        ]);

        // Auto-login after registration
        Auth::login($user);

        // If there are OAuth parameters, redirect back to the authorize endpoint to complete SSO
        if ($request->has('client_id') && $request->has('redirect_uri')) {
            $query = http_build_query($request->only(['client_id', 'redirect_uri', 'response_type', 'scope', 'state']));
            return redirect('/oauth/authorize?' . $query);
        }

        return redirect('/'); // Fallback if not coming from an SSO flow
    }
}
