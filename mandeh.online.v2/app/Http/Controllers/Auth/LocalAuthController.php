<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LocalAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email_or_phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $field = filter_var($request->email_or_phone, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone_number';

        if (Auth::attempt([$field => $request->email_or_phone, 'password' => $request->password])) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'email_or_phone' => __('auth.failed'),
        ]);
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'profile_type' => 'required|in:warga,pelaku_wisata',
            'origin' => 'required|in:lokal,perantau',
            'is_hpi_candidate' => 'required|in:0,1',
            'armada' => 'required_if:is_hpi_candidate,1|nullable|in:darat_laut,udara',
            'profession' => 'required|string|max:255',
            'profession_detail' => 'nullable|string|max:255',
            'initial_rating' => 'required|integer|min:1|max:5',
            'nagari' => 'required|string|max:255',
            'kecamatan' => 'required|string|max:255',
            'kabupaten' => 'required|string|max:255',
            'provinsi' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'full_address' => 'required|string',
            'join_reason' => 'required|string',
            'gender' => 'required|in:L,P',
            'date_of_birth' => 'required|date',
            'selfie' => 'required|image|mimes:jpeg,png,jpg|max:10240',
            'ktp' => 'required|image|mimes:jpeg,png,jpg|max:10240',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $isHpiCandidate = (bool)(int)$request->is_hpi_candidate;
        $armada = $request->armada ?? 'darat_laut';

        if ($isHpiCandidate) {
            $count = \App\Modules\Vanguard\Models\VanguardParticipant::where('is_hpi_candidate', true)->where('armada', $armada)->count();

            if ($armada === 'darat_laut' && $count >= 50) {
                throw ValidationException::withMessages([
                    'armada' => 'Kuota Armada Darat & Laut sudah penuh (Maksimal 50 peserta). Pendaftaran ditutup.',
                ]);
            }

            if ($armada === 'udara' && $count >= 100) {
                throw ValidationException::withMessages([
                    'armada' => 'Kuota Armada Udara sudah penuh (Maksimal 100 peserta). Pendaftaran ditutup.',
                ]);
            }
        }

        $targetRole = $request->profile_type === 'warga' ? 'warga' : 'business_owner';
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'role' => $targetRole,
            'is_verified' => false,
        ]);

        // Save GPS log tracking
        \App\Models\UserLogTracking::create([
            'user_id' => $user->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        // Store selfie and KTP
        $selfiePath = $request->file('selfie')->store('uploads/vanguard', 'public');
        $ktpPath = $request->file('ktp')->store('uploads/vanguard', 'public');

        $dateStr = date('Ymd');
        $randomSuffix = str_pad((string)random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        $trackingId = "VGD-{$dateStr}-{$randomSuffix}";

        \App\Modules\Vanguard\Models\VanguardParticipant::create([
            'user_id' => $user->id,
            'tracking_id' => $trackingId,
            'name' => $user->name,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'origin' => $request->origin,
            'armada' => $armada,
            'profile_type' => $request->profile_type,
            'is_hpi_candidate' => $isHpiCandidate,
            'profession' => $request->profession,
            'profession_detail' => $request->profession_detail,
            'initial_rating' => (int)$request->initial_rating,
            'reputation_points' => 0,
            'is_vanguard_graduated' => false,
            'is_inaugurated' => false,
            'nagari' => $request->nagari,
            'kecamatan' => $request->kecamatan,
            'kabupaten' => $request->kabupaten,
            'provinsi' => $request->provinsi,
            'postal_code' => $request->postal_code,
            'full_address' => $request->full_address,
            'join_reason' => $request->join_reason,
            'selfie_path' => $selfiePath,
            'ktp_path' => $ktpPath,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Pendaftaran Akun & Berkas Calon Anggota berhasil dikirim! Menunggu persetujuan Admin.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function checkUniqueness(Request $request)
    {
        $email = $request->email;
        $phone = $request->phone_number;

        $emailExists = false;
        if ($email) {
            $emailExists = User::where('email', $email)->exists();
        }

        $phoneExists = false;
        if ($phone) {
            $phoneExists = User::where('phone_number', $phone)->exists();
        }

        return response()->json([
            'email_exists' => $emailExists,
            'phone_exists' => $phoneExists,
        ]);
    }

    public function getProvinces()
    {
        $data = \Illuminate\Support\Facades\Cache::remember('wilayah_provinces', 86400 * 30, function () {
            $response = \Illuminate\Support\Facades\Http::get('https://emsifa.github.io/api-wilayah-indonesia/api/provinces.json');
            return $response->successful() ? $response->json() : [];
        });
        return response()->json($data);
    }

    public function getRegencies($provinceId)
    {
        if (!preg_match('/^[0-9]+$/', $provinceId)) {
            return response()->json([], 400);
        }
        $data = \Illuminate\Support\Facades\Cache::remember("wilayah_regencies_{$provinceId}", 86400 * 30, function () use ($provinceId) {
            $response = \Illuminate\Support\Facades\Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/regencies/{$provinceId}.json");
            return $response->successful() ? $response->json() : [];
        });
        return response()->json($data);
    }

    public function getDistricts($regencyId)
    {
        if (!preg_match('/^[0-9]+$/', $regencyId)) {
            return response()->json([], 400);
        }
        $data = \Illuminate\Support\Facades\Cache::remember("wilayah_districts_{$regencyId}", 86400 * 30, function () use ($regencyId) {
            $response = \Illuminate\Support\Facades\Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/districts/{$regencyId}.json");
            return $response->successful() ? $response->json() : [];
        });
        return response()->json($data);
    }

    public function getVillages($districtId)
    {
        if (!preg_match('/^[0-9]+$/', $districtId)) {
            return response()->json([], 400);
        }
        $data = \Illuminate\Support\Facades\Cache::remember("wilayah_villages_{$districtId}", 86400 * 30, function () use ($districtId) {
            $response = \Illuminate\Support\Facades\Http::get("https://emsifa.github.io/api-wilayah-indonesia/api/villages/{$districtId}.json");
            return $response->successful() ? $response->json() : [];
        });
        return response()->json($data);
    }
}
