<?php

declare(strict_types=1);

namespace App\Modules\Vanguard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vanguard\Models\VanguardParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class VanguardOnboardingController extends Controller
{
    public function showRegistrationForm()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $participant = VanguardParticipant::where('user_id', $user->id)->first();

        if ($participant) {
            return redirect()->route('vanguard.dashboard');
        }

        return view('vanguard::register', ['user' => $user]);
    }

    public function register(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        $request->validate([
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

        $existing = VanguardParticipant::where('user_id', $user->id)->first();
        if ($existing) {
            return redirect()->route('vanguard.dashboard');
        }

        $isHpiCandidate = (bool)(int)$request->is_hpi_candidate;
        $armada = $request->armada ?? 'darat_laut';

        if ($isHpiCandidate) {
            $count = VanguardParticipant::where('is_hpi_candidate', true)->where('armada', $armada)->count();

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

        // Store selfie and KTP
        $selfiePath = $request->file('selfie')->store('uploads/vanguard', 'public');
        $ktpPath = $request->file('ktp')->store('uploads/vanguard', 'public');

        // Generate Tracking ID: VGD-YYYYMMDD-XXXX
        $dateStr = date('Ymd');
        $randomSuffix = str_pad((string)random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        $trackingId = "VGD-{$dateStr}-{$randomSuffix}";

        VanguardParticipant::create([
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

        // Save GPS log tracking
        \App\Models\UserLogTracking::create([
            'user_id' => $user->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        // Update user role based on choice
        $targetRole = $request->profile_type === 'warga' ? 'warga' : 'business_owner';
        $user->update([
            'role' => $targetRole,
            'is_verified' => false, // Ensure verification remains pending until approved by admin
        ]);

        return redirect()->route('dashboard')->with('success', 'Pendaftaran Anda berhasil dikirim! Menunggu persetujuan Admin.');
    }
}
