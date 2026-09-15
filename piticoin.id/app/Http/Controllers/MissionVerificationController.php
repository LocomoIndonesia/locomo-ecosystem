<?php

namespace App\Http\Controllers;

use App\Models\MissionVerification;
use App\Models\MissionVerificationJuror;
use App\Services\MissionVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Exception;

class MissionVerificationController extends Controller
{
    protected MissionVerificationService $verificationService;

    public function __construct(MissionVerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    /**
     * Dashboard Juri & Daftar Verifikasi Misi
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $pendingTasks = $this->verificationService->getPendingTasksForUser($user);
        $jurorHistory = $this->verificationService->getAllJurorHistoryForUser($user);
        $mySubmissions = $this->verificationService->getMySubmissions($user);

        $wallet = $user->wallet;
        $reputation = $user->reputation;

        return Inertia::render('Verifications/Index', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'role' => $user->role,
                'license_status' => $user->license_status,
                'jury_banned_until' => $user->jury_banned_until ? $user->jury_banned_until->toIso8601String() : null,
                'is_jury_banned' => $user->isJuryBanned(),
                'experience_points' => $user->experience_points,
            ],
            'wallet' => [
                'silver_balance' => $wallet ? $wallet->silver_balance : 0,
                'gold_balance' => $wallet ? $wallet->gold_balance : 0,
            ],
            'reputation_score' => $reputation ? $reputation->total_score : 0,
            'pending_tasks' => $pendingTasks,
            'juror_history' => $jurorHistory,
            'my_submissions' => $mySubmissions,
            'stats' => [
                'pending_count' => $pendingTasks->count(),
                'completed_count' => $jurorHistory->where('vote', '!=', 'PENDING')->count(),
                'slashed_count' => $jurorHistory->where('is_slashed', true)->count(),
            ],
        ]);
    }

    /**
     * Halaman Detail Blind Verification untuk Juri
     */
    public function show(Request $request, string $id)
    {
        $user = Auth::user();
        $verification = MissionVerification::with(['quest', 'jurors'])->findOrFail($id);

        // Cari apakah user saat ini adalah juri yang ditugaskan
        $myAssignment = $verification->jurors->firstWhere('juror_user_id', $user->id);

        // Jika user adalah submitter, boleh lihat
        $isSubmitter = ($verification->submitter_user_id === $user->id);

        if (!$myAssignment && !$isSubmitter && $user->role !== 'ADMIN') {
            return redirect()->route('verifications.index')->with('error', 'Anda tidak memiliki hak akses untuk sesi juri misi ini.');
        }

        // Masking submitter untuk blind validation juri (hanya tampil jika konsensus sudah selesai atau user adalah admin)
        $showRealSubmitter = $verification->isResolved() || $isSubmitter || $user->role === 'ADMIN';

        return Inertia::render('Verifications/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'is_jury_banned' => $user->isJuryBanned(),
            ],
            'wallet' => [
                'silver_balance' => $user->wallet ? $user->wallet->silver_balance : 0,
            ],
            'verification' => [
                'id' => $verification->id,
                'mission_title' => $verification->mission_title,
                'proof_description' => $verification->proof_description,
                'proof_image_url' => $verification->proof_image_url,
                'reward_gold' => $verification->reward_gold,
                'reward_silver' => $verification->reward_silver,
                'status' => $verification->status,
                'valid_votes_count' => $verification->valid_votes_count,
                'reject_votes_count' => $verification->reject_votes_count,
                'total_jurors_count' => $verification->total_jurors_count,
                'is_resolved' => $verification->isResolved(),
                'consensus_reached_at' => $verification->consensus_reached_at ? $verification->consensus_reached_at->toIso8601String() : null,
                'created_at' => $verification->created_at->toIso8601String(),
                'submitter_name' => $showRealSubmitter ? ($verification->submitter->name ?? 'Anonim') : $verification->masked_submitter,
                'is_blind' => !$showRealSubmitter,
            ],
            'my_assignment' => $myAssignment ? [
                'id' => $myAssignment->id,
                'vote' => $myAssignment->vote,
                'stake_amount_silver' => $myAssignment->stake_amount_silver,
                'reward_amount_silver' => $myAssignment->reward_amount_silver,
                'is_slashed' => $myAssignment->is_slashed,
                'voted_at' => $myAssignment->voted_at ? $myAssignment->voted_at->toIso8601String() : null,
                'notes' => $myAssignment->notes,
            ] : null,
            'is_submitter' => $isSubmitter,
        ]);
    }

    /**
     * Submit Suara Juri (VALID atau REJECT) dengan Pasak Staking 50 Perak
     */
    public function submitVote(Request $request, string $id)
    {
        $request->validate([
            'vote' => 'required|in:VALID,REJECT',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        if ($user->isJuryBanned()) {
            return back()->with('error', 'Status Juri Anda sedang dibekukan akibat pelanggaran honeypot hingga ' . $user->jury_banned_until->format('d M Y H:i'));
        }

        $verification = MissionVerification::findOrFail($id);

        try {
            $result = $this->verificationService->castVote(
                $user,
                $verification,
                $request->input('vote'),
                $request->input('notes')
            );

            if ($result['fell_for_honeypot']) {
                return redirect()->route('verifications.index')->with('warning', 'PERINGATAN KERAS: Anda menyetujui misi palsu (Honeypot Trap)! Pasak 50 Perak hangus, reputasi Anda dipotong 100 poin, dan hak juri diskors selama 7 hari.');
            }

            if ($result['consensus_reached']) {
                $statusText = $result['new_status'] === 'CONSENSUS_APPROVED' ? 'DITERIMA' : 'DITOLAK';
                return redirect()->route('verifications.show', $id)->with('success', "Suara Anda berhasil dicatat! Konsensus 5 juri telah tercapai: Misi {$statusText}. Dividen konsensus telah didistribusikan.");
            }

            return redirect()->route('verifications.show', $id)->with('success', 'Suara Anda berhasil dicatat dengan pasak 50 Koin Perak. Menunggu suara juri lainnya.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Submit Pengajuan Bukti Misi Baru
     */
    public function submitProof(Request $request)
    {
        $request->validate([
            'mission_title' => 'required|string|max:150',
            'proof_description' => 'required|string|max:1000',
            'proof_image' => 'nullable|image|max:5120', // maks 5MB
            'reward_gold' => 'nullable|integer|min:0|max:10000',
            'reward_silver' => 'nullable|integer|min:0|max:50000',
        ]);

        $user = Auth::user();

        $imageUrl = null;
        if ($request->hasFile('proof_image')) {
            $path = $request->file('proof_image')->store('proofs', 'public');
            $imageUrl = '/storage/' . $path;
        }

        try {
            $verification = $this->verificationService->submitProof($user, [
                'mission_title' => $request->input('mission_title'),
                'proof_description' => $request->input('proof_description'),
                'proof_image_url' => $imageUrl,
                'reward_gold' => $request->input('reward_gold', 50),
                'reward_silver' => $request->input('reward_silver', 500),
            ]);

            return redirect()->route('verifications.index')->with('success', 'Pengajuan bukti misi berhasil dikirim! 5 Juri acak telah ditugaskan dan dinotifikasi secara real-time via WebPush.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal mengajukan misi: ' . $e->getMessage());
        }
    }
}
