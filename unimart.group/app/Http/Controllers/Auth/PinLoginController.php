<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PinLoginController extends Controller
{
    /**
     * Menampilkan halaman login PIN.
     */
    public function show()
    {
        return view('auth.pin-login');
    }

    /**
     * Memproses login berbasis PIN.
     */
    public function login(Request $request)
    {
        $request->validate([
            'pin' => 'required|string|between:4,6',
        ]);

        $tenantId = app('tenant_id');
        $tenant = app('tenant');

        if (!$tenantId || !$tenant) {
            return back()->withErrors(['pin' => 'Tenant tidak teridentifikasi. Harap akses melalui domain toko.']);
        }

        // Cari user di tenant aktif yang memiliki PIN yang sesuai
        $user = User::where('tenant_id', $tenantId)
                    ->where('pin', $request->pin)
                    ->first();

        if (!$user) {
            return back()->withErrors(['pin' => 'PIN salah atau tidak terdaftar untuk staf toko ini.']);
        }

        // Login staf
        Auth::login($user);

        $alias = $tenant->domain;

        // Redirect berdasarkan role ke rute dinamis kafe
        if ($user->isOwner()) {
            return redirect()->intended('/owner/dashboard');
        } elseif ($user->isCashier()) {
            return redirect()->intended('/caffe/' . $alias . '/pos');
        } elseif ($user->isChef()) {
            return redirect()->intended('/caffe/' . $alias . '/kds');
        } elseif ($user->isWaiter()) {
            return redirect()->intended('/caffe/' . $alias);
        }

        return redirect('/caffe/' . $alias);
    }

    /**
     * Logout staf.
     */
    public function logout()
    {
        Auth::logout();
        return redirect('/login-pin');
    }
}
