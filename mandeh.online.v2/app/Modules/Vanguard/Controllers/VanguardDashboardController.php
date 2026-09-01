<?php

declare(strict_types=1);

namespace App\Modules\Vanguard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vanguard\Models\VanguardParticipant;
use App\Modules\Vanguard\Models\VanguardCheckin;
use App\Modules\Vanguard\Models\VanguardMission;
use App\Modules\Vanguard\Models\VanguardMissionReport;
use App\Modules\Vanguard\Models\VanguardSurvey;
use App\Modules\Vanguard\Models\VanguardVote;
use App\Modules\Vanguard\Models\VanguardVoteParticipation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class VanguardDashboardController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $participant = VanguardParticipant::where('user_id', $user->id)->first();

        if (!$participant) {
            return redirect()->route('vanguard.register');
        }

        // Seed default missions if none exist
        if (VanguardMission::count() === 0) {
            VanguardMission::create([
                'title' => 'Simulasi Pelayanan Tamu di Kawasan Mandeh',
                'description' => 'Latih kemampuan pemanduan Anda dengan mensimulasikan pelayanan tamu secara nyata sesuai standar HPI.',
                'armada_type' => 'darat_laut',
                'points_reward' => 30,
                'start_time' => Carbon::now()->subHours(1),
                'end_time' => Carbon::now()->addDays(5),
            ]);
            VanguardMission::create([
                'title' => 'Kampanye Kebangsaan & Kode Etik HPI Pessel',
                'description' => 'Ikuti diskusi materi tentang wawasan kebangsaan dan kode etik profesi pramuwisata.',
                'armada_type' => 'both',
                'points_reward' => 20,
                'start_time' => Carbon::now()->subHours(1),
                'end_time' => Carbon::now()->addDays(5),
            ]);
            VanguardMission::create([
                'title' => 'Kampanye Media Sosial Wisata Nagari',
                'description' => 'Buat infografis atau tulisan promosi wisata nagari terintegrasi di media sosial untuk menarik traffic.',
                'armada_type' => 'udara',
                'points_reward' => 35,
                'start_time' => Carbon::now()->subHours(1),
                'end_time' => Carbon::now()->addDays(5),
            ]);
        }

        // Fetch and map missions with status countdown
        $allMissions = VanguardMission::whereIn('armada_type', [$participant->armada, 'both'])->get();
        $missions = $allMissions->map(function ($mission) {
            $now = Carbon::now();
            $mission->is_active = $mission->start_time ? $now->between($mission->start_time, $mission->end_time) : true;
            $mission->not_started = $mission->start_time ? $now->lessThan($mission->start_time) : false;
            $mission->is_ended = $mission->end_time ? $now->greaterThan($mission->end_time) : false;
            return $mission;
        });

        $checkins = $participant->checkins()->orderBy('checkin_date', 'desc')->get();
        $reports = $participant->missionReports()->with('mission')->orderBy('created_at', 'desc')->get();

        $checkedInToday = $participant->checkins()->where('checkin_date', date('Y-m-d'))->exists();

        // Load targeted surveys/voting
        $allSurveys = VanguardSurvey::all();
        $surveys = $allSurveys->filter(function ($survey) use ($user) {
            $roles = $survey->target_roles ?? [];
            return in_array('all', $roles) || in_array($user->role, $roles) || in_array('vanguard', $roles);
        });

        // Map surveys to check if user has already voted, and decrypt dynamic options for results
        $surveys = $surveys->map(function ($survey) use ($user, $participant) {
            $votedCount = $survey->participations()->count();
            $hasVoted = VanguardVoteParticipation::where('survey_id', $survey->id)
                ->where('user_id', $user->id)
                ->exists();
            
            $survey->has_voted = $hasVoted;
            
            // Get user's current choice if voted
            $userChoice = null;
            if ($hasVoted) {
                $trackingId = $participant ? $participant->tracking_id : $user->id;
                $voterHash = hash('sha256', $trackingId . $survey->id . config('app.key'));
                $vote = VanguardVote::where('survey_id', $survey->id)
                    ->where('voter_hash', $voterHash)
                    ->first();
                if ($vote) {
                    try {
                        $userChoice = $vote->getChoice();
                    } catch (\Exception $e) {
                        // ignore
                    }
                }
            }
            $survey->user_choice = $userChoice;

            // Decrypt votes and aggregate
            $results = [];
            $options = $survey->options ?? [];
            foreach ($options as $opt) {
                if (isset($opt['title'])) {
                    $results[$opt['title']] = 0;
                }
            }

            $votes = $survey->votes;
            foreach ($votes as $vote) {
                try {
                    $choice = $vote->getChoice();
                    if (!isset($results[$choice])) {
                        $results[$choice] = 0;
                    }
                    $results[$choice]++;
                } catch (\Exception $e) {
                    // Ignore decryption failures
                }
            }

            $eligibleCount = $survey->getEligibleUsersCount();
            $participationRate = $eligibleCount > 0 ? ($votedCount / $eligibleCount) * 100 : 0;

            $survey->voted_count = $votedCount;
            $survey->eligible_count = $eligibleCount;
            $survey->participation_rate = round($participationRate, 1);
            $survey->vote_results = $results;

            $survey->is_closed = $survey->isClosed($votedCount);
            $survey->not_started = Carbon::now()->lessThan($survey->start_time);
            $survey->is_active = !$survey->is_closed && Carbon::now()->between($survey->start_time, $survey->end_time);
            $survey->is_ended = $survey->is_closed || Carbon::now()->greaterThan($survey->end_time);

            return $survey;
        });

        $destinations = \App\Models\Entity::where('type', 'destinasi')->latest()->get();

        return view('vanguard::dashboard', [
            'participant' => $participant,
            'missions' => $missions,
            'checkins' => $checkins,
            'reports' => $reports,
            'checkedInToday' => $checkedInToday,
            'surveys' => $surveys,
            'destinations' => $destinations,
        ]);
    }

    public function checkin(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $participant = VanguardParticipant::where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'method' => 'required|in:face_to_face,livestream',
            'gps_lat' => 'nullable|numeric',
            'gps_lng' => 'nullable|numeric',
        ]);

        $today = date('Y-m-d');

        $exists = VanguardCheckin::where('participant_id', $participant->id)
            ->where('checkin_date', $today)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Anda sudah melakukan check-in hari ini.');
        }

        // GPS Verification for Armada Darat & Laut (within 100m of Gedung TIC Mandeh: -1.3470, 100.5590)
        if ($participant->armada === 'darat_laut') {
            if (!$request->gps_lat || !$request->gps_lng) {
                throw ValidationException::withMessages([
                    'gps_lat' => 'Koordinat GPS wajib diaktifkan untuk check-in Armada Darat & Laut.',
                ]);
            }

            $lat1 = floatval($request->gps_lat);
            $lng1 = floatval($request->gps_lng);
            $lat2 = -1.3470; // Gedung TIC Mandeh
            $lng2 = 100.5590;

            $earthRadius = 6371000; // meters
            $dLat = deg2rad($lat2 - $lat1);
            $dLng = deg2rad($lng2 - $lng1);
            $a = sin($dLat/2) * sin($dLat/2) +
                 cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
                 sin($dLng/2) * sin($dLng/2);
            $c = 2 * atan2(sqrt($a), sqrt(1-$a));
            $distance = $earthRadius * $c;

            if ($distance > 100) {
                throw ValidationException::withMessages([
                    'gps_lat' => 'Presensi ditolak. Lokasi Anda berada diluar radius 100m dari Gedung TIC Mandeh (Jarak Anda: ' . round($distance) . 'm).',
                ]);
            }
        }

        VanguardCheckin::create([
            'participant_id' => $participant->id,
            'checkin_date' => $today,
            'verified_method' => $request->method,
        ]);

        $participant->recalculatePoints();

        return back()->with('success', 'Presensi berhasil dicatat! Anda mendapatkan +10 Poin Reputasi.');
    }

    public function submitMission(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $participant = VanguardParticipant::where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'mission_id' => 'required|exists:vanguard_missions,id',
            'report_text' => 'required|string|min:10',
            'proof_url' => 'nullable|url',
        ]);

        $mission = VanguardMission::findOrFail($request->mission_id);

        // Verify mission is active
        $now = Carbon::now();
        if ($mission->start_time && ($now->lessThan($mission->start_time) || $now->greaterThan($mission->end_time))) {
            return back()->with('error', 'Misi tidak dalam rentang waktu aktif.');
        }

        VanguardMissionReport::create([
            'participant_id' => $participant->id,
            'mission_id' => $mission->id,
            'status' => 'pending', // Pending verification by Admin Manager
            'report_text' => $request->report_text,
            'proof_url' => $request->proof_url,
            'points_awarded' => $mission->points_reward, // Track potential points
        ]);

        // Recalculate will not count pending reports
        $participant->recalculatePoints();

        return back()->with('success', 'Laporan misi berhasil dikirim! Menunggu verifikasi dan penilaian dari Admin Manager.');
    }

    public function vote(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $participant = VanguardParticipant::where('user_id', $user->id)->first();

        $request->validate([
            'survey_id' => 'required|exists:vanguard_surveys,id',
            'choice' => 'required|string',
        ]);

        $survey = VanguardSurvey::findOrFail($request->survey_id);
        $votedCount = $survey->participations()->count();

        // Check if the user has already voted
        $hasVoted = VanguardVoteParticipation::where('survey_id', $survey->id)
            ->where('user_id', $user->id)
            ->exists();

        // Auto-stop condition: only block new voters if the survey is already closed
        if (!$hasVoted && $survey->isClosed($votedCount)) {
            return back()->with('error', 'Voting/Survey sudah ditutup karena batas partisipasi penuh.');
        }

        // Time window check
        if (!Carbon::now()->between($survey->start_time, $survey->end_time)) {
            return back()->with('error', 'Voting/Survey tidak berada dalam waktu aktif.');
        }

        // Verify graduation if user is vanguard
        if ($participant && !$participant->is_vanguard_graduated) {
            return back()->with('error', 'Anda harus lulus LDK (Poin Reputasi >= 80) untuk berpartisipasi dalam pemilihan ini.');
        }

        // Verify choice is one of the valid options
        $options = $survey->options ?? [];
        $optionTitles = array_column($options, 'title');
        if (!in_array($request->choice, $optionTitles)) {
            return back()->with('error', 'Pilihan tidak valid.');
        }

        // Save/Update encrypted anonymous ballot using AES-256-CBC and signature hash
        $trackingId = $participant ? $participant->tracking_id : $user->id;
        VanguardVote::castVote($request->choice, $trackingId, $survey->id);

        // Record participation log if first time voting
        $hasVoted = VanguardVoteParticipation::where('survey_id', $survey->id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$hasVoted) {
            VanguardVoteParticipation::create([
                'survey_id' => $survey->id,
                'user_id' => $user->id,
                'voted_at' => Carbon::now(),
            ]);
        }

        return back()->with('success', 'Pilihan suara Anda berhasil dikirim secara anonim dan aman!');
    }
}
