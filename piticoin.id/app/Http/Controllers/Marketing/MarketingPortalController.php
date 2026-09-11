<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCommission;
use App\Models\EcosystemService;
use App\Models\OrderInstallment;
use App\Models\ServiceOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MarketingPortalController extends Controller
{
    /**
     * Dashboard Utama Admin Marketing (Affiliate Owners Portal).
     */
    public function dashboard()
    {
        $user = Auth::user();
        $isSuperAdmin = in_array($user->role, ['ADMIN', 'SUPERADMIN', 'OWNER']) || $user->is_root;

        // Ambil layanan yang dimiliki user ini (atau semua layanan jika superadmin)
        $servicesQuery = EcosystemService::query()
            ->when(!$isSuperAdmin, function ($q) use ($user) {
                return $q->where('owner_id', $user->id);
            });

        $services = (clone $servicesQuery)->withCount(['orders', 'licenses'])->latest()->get();
        $serviceIds = $services->pluck('id')->toArray();

        // Metrik Finansial
        $ordersQuery = ServiceOrder::whereIn('service_id', $serviceIds);
        $totalIncomeGold = (float) (clone $ordersQuery)->sum('paid_amount_gold');
        $totalIncomeFiat = (float) (clone $ordersQuery)->sum('paid_amount_fiat');
        $totalOrdersCount = (clone $ordersQuery)->count();
        $activeInstallmentsCount = (clone $ordersQuery)
            ->where('payment_type', 'INSTALLMENT')
            ->where('status', '!=', 'COMPLETED')
            ->count();

        // Metrik Payout Komisi
        $commissionsQuery = AffiliateCommission::whereIn('service_id', $serviceIds);
        $totalPayoutGold = (float) (clone $commissionsQuery)->where('currency', 'GOLD')->sum('amount_gold');
        $totalPayoutFiat = (float) (clone $commissionsQuery)->where('currency', 'IDR')->sum('amount_fiat');

        // Transaksi & Komisi Terakhir
        $recentCommissions = AffiliateCommission::whereIn('service_id', $serviceIds)
            ->with(['uplineUser:id,name,username,whatsapp', 'beneficiaryUser:id,name,username', 'service:id,name,code'])
            ->latest()
            ->limit(10)
            ->get();

        return Inertia::render('Marketing/Dashboard', [
            'metrics' => [
                'total_income_gold' => $totalIncomeGold,
                'total_income_fiat' => $totalIncomeFiat,
                'total_payout_gold' => $totalPayoutGold,
                'total_payout_fiat' => $totalPayoutFiat,
                'total_orders' => $totalOrdersCount,
                'active_installments' => $activeInstallmentsCount,
            ],
            'services' => $services,
            'recent_commissions' => $recentCommissions,
            'is_super_admin' => $isSuperAdmin,
        ]);
    }

    /**
     * Halaman Tambah Layanan / Produk Baru.
     */
    public function createService()
    {
        return Inertia::render('Marketing/ServiceForm', [
            'service' => null,
            'is_edit' => false,
        ]);
    }

    /**
     * Simpan Layanan / Produk Baru ke Database.
     */
    public function storeService(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'service_type' => 'required|in:INTERNAL,EXTERNAL',
            'description' => 'required|string|max:2000',
            'icon' => 'nullable|string|max:50',
            'price_gold' => 'required|numeric|min:0',
            'price_fiat' => 'required|numeric|min:0',
            'allows_installments' => 'boolean',
            'min_down_payment_gold' => 'nullable|numeric|min:0',
            'min_down_payment_fiat' => 'nullable|numeric|min:0',
            'commission_rules' => 'nullable|array',
            'service_url' => 'nullable|string|max:255',
            'webhook_url' => 'nullable|string|max:255',
        ]);

        $code = Str::slug($request->input('name'));
        $counter = 1;
        while (EcosystemService::where('code', $code)->exists()) {
            $counter++;
            $code = Str::slug($request->input('name')) . '-' . $counter;
        }

        $service = EcosystemService::create([
            'owner_id' => Auth::id(),
            'name' => $request->input('name'),
            'code' => $code,
            'icon' => $request->input('icon') ?: ($request->input('service_type') === 'INTERNAL' ? 'card_travel' : 'hub'),
            'category' => strtoupper($request->input('category')),
            'service_type' => $request->input('service_type'),
            'description' => $request->input('description'),
            'price_gold' => (float) $request->input('price_gold'),
            'price_fiat' => (float) $request->input('price_fiat'),
            'service_url' => $request->input('service_url'),
            'webhook_url' => $request->input('webhook_url'),
            'allows_installments' => (bool) $request->input('allows_installments', false),
            'min_down_payment_gold' => $request->input('min_down_payment_gold'),
            'min_down_payment_fiat' => $request->input('min_down_payment_fiat'),
            'commission_rules' => $request->input('commission_rules'),
            'status' => 'ACTIVE',
            'is_active' => true,
        ]);

        return redirect()->route('marketing.dashboard')->with('success', 'Produk / Layanan ' . $service->name . ' berhasil diterbitkan!');
    }

    /**
     * Halaman Edit Layanan / Produk.
     */
    public function editService($id)
    {
        $service = EcosystemService::findOrFail($id);
        $user = Auth::user();

        if ($service->owner_id !== $user->id && !in_array($user->role, ['ADMIN', 'SUPERADMIN', 'OWNER']) && !$user->is_root) {
            abort(403, 'Akses ditolak. Anda bukan pemilik produk ini.');
        }

        return Inertia::render('Marketing/ServiceForm', [
            'service' => $service,
            'is_edit' => true,
        ]);
    }

    /**
     * Update Layanan / Produk (Harga Dinamis & Aturan Komisi).
     */
    public function updateService(Request $request, $id)
    {
        $service = EcosystemService::findOrFail($id);
        $user = Auth::user();

        if ($service->owner_id !== $user->id && !in_array($user->role, ['ADMIN', 'SUPERADMIN', 'OWNER']) && !$user->is_root) {
            abort(403, 'Akses ditolak. Anda bukan pemilik produk ini.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'service_type' => 'required|in:INTERNAL,EXTERNAL',
            'description' => 'required|string|max:2000',
            'icon' => 'nullable|string|max:50',
            'price_gold' => 'required|numeric|min:0',
            'price_fiat' => 'required|numeric|min:0',
            'allows_installments' => 'boolean',
            'min_down_payment_gold' => 'nullable|numeric|min:0',
            'min_down_payment_fiat' => 'nullable|numeric|min:0',
            'commission_rules' => 'nullable|array',
            'service_url' => 'nullable|string|max:255',
            'webhook_url' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $service->update([
            'name' => $request->input('name'),
            'category' => strtoupper($request->input('category')),
            'service_type' => $request->input('service_type'),
            'description' => $request->input('description'),
            'icon' => $request->input('icon') ?: $service->icon,
            'price_gold' => (float) $request->input('price_gold'),
            'price_fiat' => (float) $request->input('price_fiat'),
            'service_url' => $request->input('service_url'),
            'webhook_url' => $request->input('webhook_url'),
            'allows_installments' => (bool) $request->input('allows_installments', false),
            'min_down_payment_gold' => $request->input('min_down_payment_gold'),
            'min_down_payment_fiat' => $request->input('min_down_payment_fiat'),
            'commission_rules' => $request->input('commission_rules'),
            'is_active' => (bool) $request->input('is_active', true),
        ]);

        return redirect()->route('marketing.dashboard')->with('success', 'Pengaturan produk ' . $service->name . ' berhasil diperbarui!');
    }

    /**
     * Halaman Transaksi & Distribusi Komisi Terperinci.
     */
    public function transactions(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = in_array($user->role, ['ADMIN', 'SUPERADMIN', 'OWNER']) || $user->is_root;

        $servicesQuery = EcosystemService::query()
            ->when(!$isSuperAdmin, function ($q) use ($user) {
                return $q->where('owner_id', $user->id);
            });
        $serviceIds = $servicesQuery->pluck('id')->toArray();

        $orders = ServiceOrder::whereIn('service_id', $serviceIds)
            ->with(['service:id,name,code', 'user:id,name,username,whatsapp', 'registeredBy:id,name,username', 'installments'])
            ->latest()
            ->paginate(15);

        $commissions = AffiliateCommission::whereIn('service_id', $serviceIds)
            ->with(['service:id,name,code', 'uplineUser:id,name,username,whatsapp', 'beneficiaryUser:id,name,username'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Marketing/Transactions', [
            'orders' => $orders,
            'commissions' => $commissions,
        ]);
    }
}
