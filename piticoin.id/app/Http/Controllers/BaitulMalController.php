<?php

namespace App\Http\Controllers;

use App\Models\BaitulMalDonation;
use App\Models\BaitulMalPeriod;
use App\Services\BaitulMalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Exception;

class BaitulMalController extends Controller
{
    /**
     * Tampilkan Portal Baitul Mal & Transparansi Donasi.
     */
    public function index()
    {
        BaitulMalService::seedSamplePeriodsIfEmpty();

        $user = Auth::user();
        $pool = BaitulMalService::getPool();

        $recentDonations = BaitulMalDonation::orderBy('created_at', 'desc')
            ->take(30)
            ->get();

        $periods = BaitulMalPeriod::with(['distributions' => function ($q) {
            $q->orderBy('disbursed_at', 'desc');
        }])
            ->orderBy('distribution_date', 'desc')
            ->get();

        return Inertia::render('BaitulMal/Index', [
            'user' => $user,
            'wallet' => $user ? $user->wallet : null,
            'pool' => $pool,
            'recent_donations' => $recentDonations,
            'periods' => $periods,
        ]);
    }

    /**
     * Submit sedekah Koin Emas.
     */
    public function donate(Request $request)
    {
        $request->validate([
            'amount_gold' => 'required|integer|min:1|max:1000000',
            'prayer_message' => 'nullable|string|max:255',
            'is_anonymous' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Anda harus login terlebih dahulu.'], 401);
        }

        try {
            $donation = BaitulMalService::donate(
                $user,
                intval($request->input('amount_gold')),
                $request->input('prayer_message'),
                (bool) $request->input('is_anonymous', false)
            );

            return back()->with('success', "Alhamdulillah! Sedekah sebesar {$donation->amount_gold} Koin Emas berhasil disalurkan ke kas Baitul Mal.");
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Aamiin-kan doa donatur.
     */
    public function aamiin($id)
    {
        try {
            $newCount = BaitulMalService::aamiin($id);
            return response()->json(['success' => true, 'aamiin_count' => $newCount]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => 'Gagal mengaminkan doa.'], 400);
        }
    }
}
