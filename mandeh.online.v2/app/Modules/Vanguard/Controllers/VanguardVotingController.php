<?php

declare(strict_types=1);

namespace App\Modules\Vanguard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vanguard\Models\VanguardParticipant;
use App\Modules\Vanguard\Models\VanguardVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VanguardVotingController extends Controller
{
    public function showVotingForm()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $participant = VanguardParticipant::where('user_id', $user->id)->first();

        if (!$participant) {
            return redirect()->route('vanguard.register');
        }

        // Strict Check: Passing Grade Gate (must have >= 80 points)
        if (!$participant->is_vanguard_graduated) {
            return view('vanguard::voting_locked', [
                'participant' => $participant,
                'required_points' => 80
            ]);
        }

        // Check if already voted
        if ($participant->voted_at) {
            return view('vanguard::voting_done', [
                'participant' => $participant
            ]);
        }

        // Candidates for Chairman of HPI Pessel 2026
        $candidates = [
            '1' => 'Budi Pandeka (Nagari Carocok)',
            '2' => 'Randi Sikumbang (Nagari Cubadak)',
            '3' => 'Aulia Harahap (Nagari Koto Tarusan)',
        ];

        return view('vanguard::voting', [
            'participant' => $participant,
            'candidates' => $candidates,
        ]);
    }

    public function castVote(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $participant = VanguardParticipant::where('user_id', $user->id)->firstOrFail();

        // Strict check
        if (!$participant->is_vanguard_graduated) {
            return back()->with('error', 'Akses ditolak: Poin reputasi Anda belum mencapai batas kelulusan (80 Poin).');
        }

        if ($participant->voted_at) {
            return back()->with('error', 'Anda sudah memberikan suara sebelumnya.');
        }

        $request->validate([
            'candidate' => 'required|in:1,2,3',
        ]);

        // Secure ballot casting using ISO standards
        VanguardVote::castVote($request->candidate, $participant->tracking_id);

        // Mark voter as voted
        $participant->voted_at = now();
        $participant->save();

        return redirect()->route('vanguard.voting')->with('success', 'Suara Anda telah berhasil dikirimkan secara aman dan dienkripsi dengan standar internasional.');
    }
}
