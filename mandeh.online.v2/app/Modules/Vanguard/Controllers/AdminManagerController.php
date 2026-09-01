<?php

declare(strict_types=1);

namespace App\Modules\Vanguard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vanguard\Models\VanguardSurvey;
use App\Modules\Vanguard\Models\VanguardParticipant;
use App\Modules\Vanguard\Models\VanguardMission;
use App\Modules\Vanguard\Models\VanguardMissionReport;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AdminManagerController extends Controller
{
    public function indexSurveys()
    {
        $this->authorizeAdminManager();

        // Fetch surveys and calculate real-time stats by decrypting options
        $surveys = VanguardSurvey::latest()->get()->map(function ($survey) {
            $votes = $survey->votes;
            $results = [];

            // Initialize candidate/option results with 0 votes
            $options = $survey->options ?? [];
            foreach ($options as $opt) {
                if (isset($opt['title'])) {
                    $results[$opt['title']] = 0;
                }
            }

            // Decrypt votes and aggregate
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

            $votedCount = $survey->participations()->count();
            $eligibleCount = $survey->getEligibleUsersCount();
            $participationRate = $eligibleCount > 0 ? ($votedCount / $eligibleCount) * 100 : 0;

            $survey->voted_count = $votedCount;
            $survey->eligible_count = $eligibleCount;
            $survey->participation_rate = round($participationRate, 1);
            $survey->vote_results = $results;
            $survey->is_closed = $survey->isClosed($votedCount);

            return $survey;
        });
        
        // Fetch unverified users
        $unverifiedUsers = User::where('is_verified', false)->with('vanguardParticipant')->latest()->get();

        // Fetch verified regular users (Warga & Pelaku Wisata reguler)
        $verifiedWarga = User::where('is_verified', true)
            ->where(function($q) {
                $q->whereDoesntHave('vanguardParticipant')
                  ->orWhereHas('vanguardParticipant', function ($query) {
                      $query->where('is_hpi_candidate', false);
                  });
            })
            ->with('vanguardParticipant')
            ->latest()
            ->get();

        // Fetch verified HPI Candidates, ordered by points desc
        $hpiCandidates = VanguardParticipant::whereHas('user', function ($query) {
                $query->where('is_verified', true);
            })
            ->where('is_hpi_candidate', true)
            ->with(['user', 'checkins', 'missionReports'])
            ->orderBy('reputation_points', 'desc')
            ->get();

        // Fetch graduated vanguards who are not inaugurated
        $graduatedVanguards = VanguardParticipant::where('is_vanguard_graduated', true)
            ->where('is_inaugurated', false)
            ->latest()
            ->get();

        // Fetch all activities/missions
        $missions = VanguardMission::latest()->get();

        // Fetch pending mission reports for verification
        $pendingReports = VanguardMissionReport::where('status', 'pending')
            ->with(['participant', 'mission'])
            ->latest()
            ->get();

        // Fetch all users for group management
        $allUsers = User::latest()->get();

        return view('vanguard::admin.dashboard', [
            'surveys' => $surveys,
            'unverifiedUsers' => $unverifiedUsers,
            'verifiedWarga' => $verifiedWarga,
            'hpiCandidates' => $hpiCandidates,
            'graduatedVanguards' => $graduatedVanguards,
            'missions' => $missions,
            'pendingReports' => $pendingReports,
            'allUsers' => $allUsers,
        ]);
    }

    public function storeSurvey(Request $request)
    {
        $this->authorizeAdminManager();

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_roles' => 'required|array',
            'options' => 'required|array',
            'options.*.title' => 'required|string|max:255',
            'options.*.description' => 'nullable|string',
            'options.*.profile_url' => 'nullable|url',
            'options.*.link_text' => 'nullable|string',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
        ]);

        $survey = VanguardSurvey::create([
            'title' => $request->title,
            'description' => $request->description,
            'target_roles' => $request->target_roles,
            'options' => $request->options,
            'start_time' => Carbon::parse($request->start_time),
            'end_time' => Carbon::parse($request->end_time),
        ]);

        return redirect()->back()->with('success', 'Voting/Survey berhasil dijadwalkan!');
    }

    public function updateSurvey(Request $request, $id)
    {
        $this->authorizeAdminManager();

        $survey = VanguardSurvey::findOrFail($id);

        // Mutability Lock: check if start_time has already passed
        if (Carbon::now()->greaterThanOrEqualTo($survey->start_time)) {
            throw ValidationException::withMessages([
                'start_time' => 'Kegiatan sudah berjalan. Data tidak dapat diubah lagi demi menjaga integritas data.',
            ]);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_roles' => 'required|array',
            'options' => 'required|array',
            'options.*.title' => 'required|string|max:255',
            'options.*.description' => 'nullable|string',
            'options.*.profile_url' => 'nullable|url',
            'options.*.link_text' => 'nullable|string',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
        ]);

        $survey->update([
            'title' => $request->title,
            'description' => $request->description,
            'target_roles' => $request->target_roles,
            'options' => $request->options,
            'start_time' => Carbon::parse($request->start_time),
            'end_time' => Carbon::parse($request->end_time),
        ]);

        return redirect()->back()->with('success', 'Voting/Survey berhasil diperbarui!');
    }

    public function verifyUser(Request $request, $id)
    {
        $this->authorizeAdminManager();
        $user = User::findOrFail($id);
        $user->update(['is_verified' => true]);
        return redirect()->back()->with('success', "Pengguna {$user->name} berhasil diverifikasi!");
    }

    public function inaugurateVanguard(Request $request)
    {
        $this->authorizeAdminManager();

        $request->validate([
            'participant_id' => 'required|exists:vanguard_participants,id',
        ]);

        $participant = VanguardParticipant::findOrFail($request->participant_id);

        if (!$participant->is_vanguard_graduated) {
            throw ValidationException::withMessages([
                'participant_id' => 'Peserta belum lulus Graduation Gate (Poin < 80). Pelantikan ditolak.',
            ]);
        }

        $participant->update(['is_inaugurated' => true]);

        // Promote user role to Pengurus HPI / Anggota Resmi HPI
        $user = $participant->user;
        $user->update(['role' => 'layanan_publik']); // using layanan_publik as role representation for official HPI officers

        return redirect()->back()->with('success', "Peserta {$participant->name} berhasil dilantik sebagai pengurus HPI!");
    }

    // Kegiatan / Misi (Activities) CRUD
    public function storeMission(Request $request)
    {
        $this->authorizeAdminManager();

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'armada_type' => 'required|in:darat_laut,udara,both',
            'points_reward' => 'required|integer|min:1',
            'bonus_points' => 'nullable|integer|min:0',
            'badge_id' => 'nullable|string|max:255',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
        ]);

        VanguardMission::create([
            'title' => $request->title,
            'description' => $request->description,
            'armada_type' => $request->armada_type,
            'points_reward' => $request->points_reward,
            'bonus_points' => $request->bonus_points ?? 0,
            'badge_id' => $request->badge_id,
            'admin_id' => auth()->id(),
            'start_time' => Carbon::parse($request->start_time),
            'end_time' => Carbon::parse($request->end_time),
        ]);

        return redirect()->back()->with('success', 'Kegiatan/Misi berhasil ditambahkan!');
    }

    public function updateMission(Request $request, $id)
    {
        $this->authorizeAdminManager();

        $mission = VanguardMission::findOrFail($id);

        // Mutability Lock: check if start_time has already passed
        if ($mission->start_time && Carbon::now()->greaterThanOrEqualTo($mission->start_time)) {
            throw ValidationException::withMessages([
                'start_time' => 'Kegiatan sudah berjalan. Data tidak dapat diubah lagi demi menjaga integritas data.',
            ]);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'armada_type' => 'required|in:darat_laut,udara,both',
            'points_reward' => 'required|integer|min:1',
            'bonus_points' => 'nullable|integer|min:0',
            'badge_id' => 'nullable|string|max:255',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
        ]);

        $mission->update([
            'title' => $request->title,
            'description' => $request->description,
            'armada_type' => $request->armada_type,
            'points_reward' => $request->points_reward,
            'bonus_points' => $request->bonus_points ?? 0,
            'badge_id' => $request->badge_id,
            'start_time' => Carbon::parse($request->start_time),
            'end_time' => Carbon::parse($request->end_time),
        ]);

        return redirect()->back()->with('success', 'Kegiatan/Misi berhasil diperbarui!');
    }

    public function deleteMission($id)
    {
        $this->authorizeAdminManager();

        $mission = VanguardMission::findOrFail($id);

        // Mutability Lock: check if start_time has already passed
        if ($mission->start_time && Carbon::now()->greaterThanOrEqualTo($mission->start_time)) {
            throw ValidationException::withMessages([
                'start_time' => 'Kegiatan sudah berjalan. Data tidak dapat dihapus.',
            ]);
        }

        $mission->delete();

        return redirect()->back()->with('success', 'Kegiatan/Misi berhasil dihapus!');
    }

    // Validasi Laporan Tugas
    public function approveReport(Request $request, $id)
    {
        $this->authorizeAdminManager();

        $report = VanguardMissionReport::findOrFail($id);

        if ($report->status !== 'pending') {
            return redirect()->back()->with('error', 'Laporan ini sudah divalidasi sebelumnya.');
        }

        $report->update([
            'status' => 'approved',
        ]);

        // Award points to the participant
        $participant = $report->participant;
        $participant->recalculatePoints();

        return redirect()->back()->with('success', "Laporan tugas {$participant->name} berhasil disetujui!");
    }

    public function rejectReport(Request $request, $id)
    {
        $this->authorizeAdminManager();

        $report = VanguardMissionReport::findOrFail($id);

        if ($report->status !== 'pending') {
            return redirect()->back()->with('error', 'Laporan ini sudah divalidasi sebelumnya.');
        }

        $report->update([
            'status' => 'rejected',
            'points_awarded' => 0,
        ]);

        // Recalculate participant points
        $participant = $report->participant;
        $participant->recalculatePoints();

        return redirect()->back()->with('success', "Laporan tugas {$participant->name} telah ditolak.");
    }

    // Manajemen User / Peran
    public function updateUserRole(Request $request, $id)
    {
        $this->authorizeAdminManager();

        $request->validate([
            'role' => 'required|string|in:wisatawan,warga,vanguard,business_owner,layanan_publik,sponsor,admin,admin_manager',
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'role' => $request->role,
        ]);

        return redirect()->back()->with('success', "Peran pengguna {$user->name} berhasil diubah menjadi " . ucfirst($request->role) . ".");
    }

    private function authorizeAdminManager()
    {
        $user = auth()->user();
        if (!$user || ($user->role !== 'admin' && $user->role !== 'admin_manager')) {
            abort(403, 'Akses ditolak. Hanya Admin Manager yang diizinkan.');
        }
    }
}
