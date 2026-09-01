<?php

declare(strict_types=1);

namespace App\Modules\Vanguard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vanguard\Models\VanguardParticipant;
use App\Modules\Vanguard\Models\VanguardMission;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TaskController extends Controller
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
        $checkedInToday = $participant->checkins()->where('checkin_date', date('Y-m-d'))->exists();

        return view('vanguard::tasks', [
            'participant' => $participant,
            'missions' => $missions,
            'checkins' => $checkins,
            'checkedInToday' => $checkedInToday,
        ]);
    }
}
