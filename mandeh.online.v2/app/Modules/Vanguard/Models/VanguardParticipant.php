<?php

declare(strict_types=1);

namespace App\Modules\Vanguard\Models;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VanguardParticipant extends BaseModel
{
    protected $fillable = [
        'user_id',
        'tracking_id',
        'name',
        'gender',
        'date_of_birth',
        'origin',
        'armada',
        'profile_type',
        'is_hpi_candidate',
        'profession',
        'profession_detail',
        'initial_rating',
        'reputation_points',
        'is_vanguard_graduated',
        'is_inaugurated',
        'voted_at',
        'nagari',
        'kecamatan',
        'kabupaten',
        'provinsi',
        'postal_code',
        'full_address',
        'join_reason',
        'selfie_path',
        'ktp_path',
    ];

    protected $casts = [
        'is_vanguard_graduated' => 'boolean',
        'is_inaugurated' => 'boolean',
        'is_hpi_candidate' => 'boolean',
        'initial_rating' => 'integer',
        'voted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(VanguardCheckin::class, 'participant_id');
    }

    public function missionReports(): HasMany
    {
        return $this->hasMany(VanguardMissionReport::class, 'participant_id');
    }

    /**
     * Recalculate reputation points and check graduation
     */
    public function recalculatePoints(): void
    {
        $checkinPoints = $this->checkins()->count() * 10; // 10 points per check-in
        
        $missionPoints = $this->missionReports()
            ->where('status', 'approved')
            ->sum('points_awarded');

        $total = $checkinPoints + $missionPoints;

        $this->reputation_points = $total;
        $this->is_vanguard_graduated = ($total >= 80); // default passing grade
        $this->save();
    }

    /**
     * Get dynamic badges based on reputation and registered businesses.
     */
    public function getBadgesAttribute(): array
    {
        $badges = [];
        
        // 1. Check-in badges
        $checkinsCount = $this->checkins()->count();
        if ($checkinsCount >= 1) {
            $badges[] = [
                'icon' => 'explore',
                'name' => 'Konsisten Presensi',
                'desc' => 'Telah melakukan check-in harian pertama kali.'
            ];
        }
        if ($checkinsCount >= 7) {
            $badges[] = [
                'icon' => 'verified',
                'name' => 'Warga Teladan',
                'desc' => 'Melakukan presensi harian selama 7 hari atau lebih.'
            ];
        }

        // 2. Business registration badges (owned + registered)
        $businessesCount = \App\Models\Entity::where('upline_id', $this->user_id)
            ->orWhere('owner_id', $this->user_id)
            ->count();

        if ($businessesCount >= 1) {
            $badges[] = [
                'icon' => 'storefront',
                'name' => 'Wirausaha Mandeh',
                'desc' => 'Telah mendaftarkan bisnis pertama.'
            ];
        }
        if ($businessesCount >= 3) {
            $badges[] = [
                'icon' => 'stars',
                'name' => 'Tokoh Komunitas',
                'desc' => 'Berhasil mendaftarkan 3 bisnis atau lebih.'
            ];
        }

        // 3. Level-based badges
        $level = floor($this->reputation_points / 50) + 1;
        if ($level >= 3) {
            $badges[] = [
                'icon' => 'military_tech',
                'name' => 'Pelaku Wisata Senior',
                'desc' => 'Mencapai reputasi level 3.'
            ];
        }
        if ($level >= 5) {
            $badges[] = [
                'icon' => 'workspace_premium',
                'name' => 'Legenda Mandeh',
                'desc' => 'Mencapai reputasi tingkat tinggi (level 5+).'
            ];
        }

        return $badges;
    }
}
