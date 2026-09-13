<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileService
{
    /**
     * Preset avatars available in Gunung Mas Ekosistem
     */
    public const PRESET_AVATARS = [
        'saudagar_pesisir' => [
            'key' => 'preset:saudagar_pesisir',
            'label' => 'Saudagar Pesisir',
            'faction' => 'NISNAS',
            'description' => 'Ahli navigasi perdagangan maritim dan negosiasi pasar.',
            'badge_color' => '#4682B4',
            'icon' => 'Anchor',
        ],
        'penjelajah_gunung' => [
            'key' => 'preset:penjelajah_gunung',
            'label' => 'Penjelajah Gunung Mas',
            'faction' => 'HUMAN',
            'description' => 'Pelacak jejak tambang dan pemburu peti harta karun.',
            'badge_color' => '#ffd700',
            'icon' => 'Compass',
        ],
        'pendeka_silat' => [
            'key' => 'preset:pendeka_silat',
            'label' => 'Pendeka Silat',
            'faction' => 'ANDROID',
            'description' => 'Penjaga integritas karavan dengan disiplin dan loyalitas tinggi.',
            'badge_color' => '#DC143C',
            'icon' => 'Shield',
        ],
        'pedagang_pasar' => [
            'key' => 'preset:pedagang_pasar',
            'label' => 'Pedagang Pasar Nagari',
            'faction' => 'NISNAS',
            'description' => 'Penggerak ekonomi riil dan integrator mitra UMKM Unimart.',
            'badge_color' => '#50C878',
            'icon' => 'Store',
        ],
    ];

    /**
     * Upload and save custom avatar for user
     */
    public function uploadCustomAvatar(User $user, UploadedFile $file): string
    {
        // Remove previous custom file if present
        if ($user->avatar_url && !str_starts_with($user->avatar_url, 'preset:') && !str_starts_with($user->avatar_url, 'http')) {
            $oldPath = str_replace('/storage/', '', $user->avatar_url);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $extension = $file->getClientOriginalExtension() ?: 'png';
        $filename = 'avatar_' . Str::uuid() . '.' . strtolower($extension);

        $path = $file->storeAs('avatars', $filename, 'public');
        $publicUrl = Storage::url($path);

        $user->avatar_url = $publicUrl;
        $user->save();

        return $publicUrl;
    }

    /**
     * Assign a preset persona avatar to the user
     */
    public function setPresetAvatar(User $user, string $presetKey): string
    {
        $normalizedKey = str_replace('preset:', '', $presetKey);

        if (!array_key_exists($normalizedKey, self::PRESET_AVATARS)) {
            throw new \InvalidArgumentException('Preset avatar tidak valid.');
        }

        // Clean up old custom uploaded file if exists
        if ($user->avatar_url && !str_starts_with($user->avatar_url, 'preset:') && !str_starts_with($user->avatar_url, 'http')) {
            $oldPath = str_replace('/storage/', '', $user->avatar_url);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $fullKey = 'preset:' . $normalizedKey;
        $user->avatar_url = $fullKey;
        $user->save();

        return $fullKey;
    }

    /**
     * Update user profile information
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->name = $data['name'];
        if (isset($data['username']) && !empty($data['username'])) {
            $user->username = strtolower($data['username']);
        }
        if (isset($data['audio_sfx_enabled'])) {
            $user->audio_sfx_enabled = (bool) $data['audio_sfx_enabled'];
        }
        $user->save();

        return $user;
    }

    /**
     * Toggle Audio SFX preference
     */
    public function toggleAudioSfx(User $user, bool $enabled): bool
    {
        $user->audio_sfx_enabled = $enabled;
        $user->save();

        return $enabled;
    }
}
