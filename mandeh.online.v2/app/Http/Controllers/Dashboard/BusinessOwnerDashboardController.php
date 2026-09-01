<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Modules\Vanguard\Models\VanguardParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessOwnerDashboardController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $participant = VanguardParticipant::where('user_id', $user->id)->first();

        if (!$participant) {
            return redirect()->route('tourism.register');
        }

        $checkedInToday = $participant->checkins()->where('checkin_date', date('Y-m-d'))->exists();
        $checkins = $participant->checkins()->orderBy('checkin_date', 'desc')->get();
        $businesses = \App\Models\Entity::where('upline_id', $user->id)
            ->orWhere('owner_id', $user->id)
            ->with(['owner', 'associations.user'])
            ->get();

        $complaints = \App\Models\Interaction::whereIn('entity_id', $businesses->pluck('id'))
            ->where('type', 'keluhan')
            ->with(['entity', 'user'])
            ->latest()
            ->get();

        // Fetch destinations (Entity where type = 'destinasi')
        $destinations = \App\Models\Entity::where('type', 'destinasi')->latest()->get();

        return view('dashboard.business.index', [
            'user' => $user,
            'participant' => $participant,
            'checkedInToday' => $checkedInToday,
            'checkins' => $checkins,
            'businesses' => $businesses,
            'complaints' => $complaints,
            'destinations' => $destinations,
        ]);
    }
}
