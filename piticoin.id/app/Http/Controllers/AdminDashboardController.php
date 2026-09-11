<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Consultation;
use App\Models\LiquidityPoolSystem;
use App\Models\EcosystemService;
use App\Models\ServiceOrder;
use App\Models\AffiliateCommission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    /**
     * Display the Admin Root Level 0 Management Panel.
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser || $currentUser->role !== 'ADMIN') {
            abort(403, 'Akses terbatas untuk Administrator Root Level 0 Platform.');
        }

        // 1. Fetch Users Pending Payment / Verification
        $pendingRaw = User::where('license_status', 'PENDING')
            ->with('wallet')
            ->orderBy('created_at', 'desc')
            ->get();

        $packageDetails = [
            'PEMULA' => [
                'name' => 'Starter Kit Pemula',
                'amount' => 50000,
                'formatted' => 'Rp 50.000',
                'silver' => 500,
            ],
            'PETUALANG' => [
                'name' => 'Starter Kit Petualang',
                'amount' => 100000,
                'formatted' => 'Rp 100.000',
                'silver' => 1500,
            ],
            'SAUDAGAR' => [
                'name' => 'Starter Kit Saudagar VIP',
                'amount' => 200000,
                'formatted' => 'Rp 200.000',
                'silver' => 4000,
            ],
            'NONE' => [
                'name' => 'Starter Kit Standar',
                'amount' => 50000,
                'formatted' => 'Rp 50.000',
                'silver' => 500,
            ],
        ];

        $pendingUsers = $pendingRaw->map(function ($u) use ($packageDetails) {
            $pkgKey = strtoupper($u->license_type ?: 'NONE');
            $pkg = $packageDetails[$pkgKey] ?? $packageDetails['NONE'];

            return [
                'id' => $u->id,
                'name' => $u->name,
                'username' => $u->username,
                'whatsapp' => $u->whatsapp,
                'faction' => $u->faction ?: 'NISNAS',
                'role_class' => $u->role_class ?: 'PENDEKAR',
                'license_type' => $u->license_type,
                'package_name' => $pkg['name'],
                'package_price' => $pkg['formatted'],
                'silver_reward' => $pkg['silver'],
                'registered_at' => $u->created_at ? $u->created_at->translatedFormat('d M Y, H:i') : '-',
                'waiting_since' => $u->created_at ? $u->created_at->diffForHumans() : '-',
            ];
        });

        // 2. Fetch Active Saudagars (license_status = 'ACTIVE' and role != 'ADMIN')
        $activeRaw = User::where('license_status', 'ACTIVE')
            ->where('role', '!=', 'ADMIN')
            ->with(['wallet', 'downlines', 'grandDownlines'])
            ->orderBy('created_at', 'desc')
            ->get();

        $activeSaudagars = $activeRaw->map(function ($u) {
            $l1Count = $u->downlines->count();
            $l2Count = $u->grandDownlines->count();
            $totalDownlines = $l1Count + $l2Count;

            // Scale Up Rate Formula: 10% per L1 downline, 5% per L2 downline
            $scaleUpRate = ($l1Count * 10) + ($l2Count * 5);

            $unreadConsultations = Consultation::where('user_id', $u->id)
                ->where('sender_role', 'SAUDAGAR')
                ->where('is_read', false)
                ->count();

            return [
                'id' => $u->id,
                'name' => $u->name,
                'username' => $u->username ?: '-',
                'whatsapp' => $u->whatsapp,
                'faction' => $u->faction ?: 'HUMAN',
                'role_class' => $u->role_class ?: 'PENDEKAR',
                'license_type' => $u->license_type ?: 'PEMULA',
                'level' => $u->level ?: 1,
                'experience_points' => $u->experience_points ?: 0,
                'silver_balance' => $u->wallet ? $u->wallet->silver_balance : 0,
                'gold_balance' => $u->wallet ? $u->wallet->gold_balance : 0,
                'downlines_l1' => $l1Count,
                'downlines_l2' => $l2Count,
                'total_downlines' => $totalDownlines,
                'scale_up_rate' => $scaleUpRate,
                'rating' => (float) ($u->rating ?: 5.0),
                'total_reviews' => $u->total_reviews ?: 1,
                'unread_consultations' => $unreadConsultations,
                'joined_at' => $u->created_at ? $u->created_at->translatedFormat('d M Y') : '-',
            ];
        });

        // 3. Global Stats
        $lp = LiquidityPoolSystem::find(1);
        $totalSilverCirculating = (float) Wallet::sum('silver_balance');
        $totalGoldCirculating = (float) Wallet::sum('gold_balance');

        // 4. Marketing & Ecosystem Hub Data for Root Admin
        $services = EcosystemService::with('owner:id,name,username')
            ->withCount(['orders', 'licenses'])
            ->latest()
            ->get();

        $orders = ServiceOrder::with([
            'service:id,name,code,service_type',
            'user:id,name,username,whatsapp',
            'registeredBy:id,name,username',
            'installments',
        ])
            ->latest()
            ->limit(50)
            ->get();

        $recentCommissions = AffiliateCommission::with([
            'service:id,name,code',
            'uplineUser:id,name,username,whatsapp',
            'beneficiaryUser:id,name,username',
        ])
            ->latest()
            ->limit(50)
            ->get();

        // 5. Marketing Financial Metrics
        $totalOmzetGold = (float) ServiceOrder::sum('paid_amount_gold');
        $totalOmzetFiat = (float) ServiceOrder::sum('paid_amount_fiat');
        $totalPayoutGold = (float) AffiliateCommission::where('currency', 'GOLD')->sum('amount_gold');
        $totalPayoutFiat = (float) AffiliateCommission::where('currency', 'IDR')->sum('amount_fiat');
        $activeInstallmentsCount = ServiceOrder::where('payment_type', 'INSTALLMENT')
            ->where('status', '!=', 'COMPLETED')
            ->count();

        return Inertia::render('Admin/Dashboard', [
            'adminUser' => [
                'id' => $currentUser->id,
                'name' => $currentUser->name,
                'username' => $currentUser->username,
                'role' => $currentUser->role,
                'is_root' => (bool) $currentUser->is_root,
                'rating' => (float) $currentUser->rating,
            ],
            'pendingUsers' => $pendingUsers,
            'activeSaudagars' => $activeSaudagars,
            'services' => $services,
            'orders' => $orders,
            'recentCommissions' => $recentCommissions,
            'stats' => [
                'pending_count' => $pendingUsers->count(),
                'active_count' => $activeSaudagars->count(),
                'services_count' => $services->count(),
                'orders_count' => $orders->count(),
                'active_installments' => $activeInstallmentsCount,
                'total_omzet_gold' => $totalOmzetGold,
                'total_omzet_fiat' => $totalOmzetFiat,
                'total_payout_gold' => $totalPayoutGold,
                'total_payout_fiat' => $totalPayoutFiat,
                'total_silver' => $totalSilverCirculating,
                'total_gold' => $totalGoldCirculating,
                'fiat_in_bank' => $lp ? (float) $lp->total_fiat_in_bank : 500000,
            ],
        ]);
    }

    /**
     * Approve user license & inject starter kit silver reward.
     */
    public function approveLicense(Request $request, string $userId)
    {
        $currentUser = Auth::user();
        if (!$currentUser || $currentUser->role !== 'ADMIN') {
            abort(403, 'Unauthorized.');
        }

        $user = User::findOrFail($userId);

        $silverReward = match (strtoupper($user->license_type ?: '')) {
            'PETUALANG' => 1500,
            'SAUDAGAR' => 4000,
            default => 500,
        };

        // Update user status
        $user->license_status = 'ACTIVE';
        $user->onboarding_step = 6;
        if ($user->role === 'GUEST') {
            $user->role = 'MERCHANT';
        }
        $user->save();

        // Inject starter kit silver coins
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['silver_balance' => 0, 'gold_balance' => 0]
        );
        $wallet->increment('silver_balance', $silverReward);

        // Create notification & welcome message in consultation
        Consultation::create([
            'user_id' => $user->id,
            'sender_id' => $currentUser->id,
            'sender_role' => 'ADMIN',
            'subject' => 'Aktivasi Lisensi Saudagar Sukses',
            'message' => "Selamat Saudagar {$user->name}! Verifikasi Starter Kit ({$user->license_type}) telah selesai disetujui oleh Administrator Platform. Akun Anda telah aktif dan bekal +{$silverReward} Koin Perak telah disalurkan ke Dompet Anda. Anda sekarang dapat mengakses seluruh ekspedisi, unimart, dan fitur Gunung Mas!",
            'is_read' => false,
        ]);

        return back()->with('success', "Lisensi Saudagar {$user->name} berhasil diaktifkan! Bekal +{$silverReward} Koin Perak telah disalurkan.");
    }

    /**
     * Fetch consultation thread with a specific Saudagar.
     */
    public function getConsultation(Request $request, string $userId)
    {
        $currentUser = Auth::user();
        if (!$currentUser || $currentUser->role !== 'ADMIN') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $saudagar = User::findOrFail($userId);

        // Mark incoming messages as read
        Consultation::where('user_id', $userId)
            ->where('sender_role', 'SAUDAGAR')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = Consultation::where('user_id', $userId)
            ->with('sender:id,name,username,role')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'saudagar' => [
                'id' => $saudagar->id,
                'name' => $saudagar->name,
                'username' => $saudagar->username,
                'whatsapp' => $saudagar->whatsapp,
                'faction' => $saudagar->faction,
                'role_class' => $saudagar->role_class,
                'rating' => $saudagar->rating,
            ],
            'messages' => $messages,
        ]);
    }

    /**
     * Send message/notification from Admin to Saudagar.
     */
    public function sendMessage(Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser || $currentUser->role !== 'ADMIN') {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'message' => 'required|string|max:2000',
            'subject' => 'nullable|string|max:255',
        ]);

        $msg = Consultation::create([
            'user_id' => $request->input('user_id'),
            'sender_id' => $currentUser->id,
            'sender_role' => 'ADMIN',
            'subject' => $request->input('subject', 'Konsultasi Admin'),
            'message' => $request->input('message'),
            'is_read' => false,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg->load('sender:id,name,username,role'),
            ]);
        }

        return back()->with('success', 'Pesan notifikasi/konsultasi berhasil dikirim ke Saudagar.');
    }
}
