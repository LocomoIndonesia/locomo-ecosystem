<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Modules\Vanguard\Models\VanguardParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WisatawanDashboardController extends Controller
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

        return view('dashboard.wisatawan');
    }
}
