<?php

namespace App\Http\Controllers;

use App\Http\Requests\AvatarUploadRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Services\ProfileService;
use App\Services\SessionSecurityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileService $profileService,
        protected SessionSecurityService $sessionSecurityService
    ) {}

    /**
     * Display the user's profile and identity form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $sessions = $this->sessionSecurityService->getActiveSessions($user, $request);

        return Inertia::render('Profile/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'whatsapp' => $user->whatsapp,
                'role' => $user->role,
                'faction' => $user->faction ?? 'HUMAN',
                'role_class' => $user->role_class ?? 'PENDEKAR',
                'license_type' => $user->license_type ?? 'NONE',
                'license_status' => $user->license_status ?? 'INACTIVE',
                'level' => $user->level ?? 1,
                'experience_points' => $user->experience_points ?? 0,
                'rating' => (float) ($user->rating ?? 5.0),
                'total_reviews' => (int) ($user->total_reviews ?? 0),
                'avatar_url' => $user->avatar_url,
                'audio_sfx_enabled' => (bool) ($user->audio_sfx_enabled ?? true),
            ],
            'presets' => array_values(ProfileService::PRESET_AVATARS),
            'sessions' => $sessions,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's basic profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $this->profileService->updateProfile($request->user(), $request->validated());

        return Redirect::route('profile.edit')->with('status', 'Profil berhasil diperbarui.');
    }

    /**
     * Handle custom avatar file upload.
     */
    public function updateAvatar(AvatarUploadRequest $request): RedirectResponse
    {
        $file = $request->file('avatar');
        $this->profileService->uploadCustomAvatar($request->user(), $file);

        return Redirect::route('profile.edit')->with('status', 'Foto avatar berhasil diperbarui.');
    }

    /**
     * Set a preset avatar persona.
     */
    public function updateAvatarPreset(Request $request): RedirectResponse
    {
        $request->validate([
            'preset' => ['required', 'string'],
        ]);

        $this->profileService->setPresetAvatar($request->user(), $request->input('preset'));

        return Redirect::route('profile.edit')->with('status', 'Avatar preset persona berhasil diterapkan.');
    }

    /**
     * Update audio SFX preferences.
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $request->validate([
            'audio_sfx_enabled' => ['required', 'boolean'],
        ]);

        $val = $this->profileService->toggleAudioSfx($request->user(), (bool) $request->input('audio_sfx_enabled'));

        return response()->json([
            'success' => true,
            'audio_sfx_enabled' => $val,
            'message' => 'Preferensi suara berhasil disimpan.',
        ]);
    }

    /**
     * Revoke all other active device sessions.
     */
    public function revokeOtherSessions(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $this->sessionSecurityService->revokeOtherSessions($request->user(), $request->input('password'));

        return Redirect::route('profile.edit')->with('status', 'Semua sesi pada perangkat lain berhasil dikeluarkan.');
    }

    /**
     * Delete the user's account (Right to Erasure compliance).
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
