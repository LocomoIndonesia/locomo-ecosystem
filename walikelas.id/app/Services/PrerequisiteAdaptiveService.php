<?php

namespace App\Services;

use App\Models\Competency;
use App\Models\Enrollment;
use App\Models\Mission;

class PrerequisiteAdaptiveService
{
    /**
     * Memeriksa kesiapan prasyarat kompetensi siswa dan merekomendasikan misi dasar jika belum siap (TAT-06)
     */
    public function getRecommendedMission(Enrollment $enrollment, Competency $targetCompetency): array
    {
        // 1. Jika kompetensi memiliki prasyarat
        if ($targetCompetency->prerequisite_id && $targetCompetency->prerequisite) {
            $prerequisite = $targetCompetency->prerequisite;

            // Periksa rekaman capaian siswa pada kompetensi prasyarat
            $prerequisiteState = $enrollment->masteryStates()
                ->where('competency_id', $prerequisite->id)
                ->latest('created_at')
                ->first();

            $isMastered = $prerequisiteState && in_array($prerequisiteState->level, ['indicated_mastery', 'educator_validated']);

            // Jika prasyarat belum dikuasai, alihkan rekomendasi ke misi dasar prasyarat
            if (!$isMastered) {
                $foundationalMission = Mission::where('competency_id', $prerequisite->id)
                    ->where('status', 'ready')
                    ->first();

                return [
                    'can_proceed_target' => false,
                    'reason' => 'PREREQUISITE_NOT_MET',
                    'recommended_competency' => $prerequisite,
                    'recommended_mission' => $foundationalMission,
                    'message' => "Siswa disarankan menyelesaikan misi penguatan prasyarat '{$prerequisite->title}' terlebih dahulu.",
                ];
            }
        }

        // 2. Jika prasyarat sudah tuntas atau tidak ada prasyarat
        $targetMission = Mission::where('competency_id', $targetCompetency->id)
            ->where('status', 'ready')
            ->first();

        return [
            'can_proceed_target' => true,
            'reason' => 'PREREQUISITE_MET',
            'recommended_competency' => $targetCompetency,
            'recommended_mission' => $targetMission,
            'message' => 'Siswa siap mengerjakan misi ini.',
        ];
    }
}
