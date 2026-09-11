<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ExternalMerchantController extends Controller
{
    /**
     * API Endpoint Pendaftaran Toko Pihak Ketiga (Ecosystem Hub - PitiCoin).
     * 
     * Menerima pendaftaran lisensi saudagar, otomatis membuat Tenant aktif
     * dengan path-based URL (/s/{domain}), serta membuat kredensial user owner.
     */
    public function registerStore(Request $request)
    {
        // 1. Verifikasi Keamanan Ekosistem
        $secret = env('LOCOMO_ECOSYSTEM_SECRET', 'locomo_ecosystem_secret_key_2026');
        $incomingSecret = $request->header('X-Locomo-Secret') ?: $request->bearerToken();

        if (!$incomingSecret || !hash_equals($secret, $incomingSecret)) {
            Log::warning('Akses pendaftaran ekosistem Unimart ditolak. Secret tidak valid.', [
                'ip' => $request->ip(),
                'received_secret' => $incomingSecret ? 'EXISTS_MISMATCH' : 'MISSING',
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Otorisasi ekosistem gagal. Token keamanan tidak valid.',
            ], 401);
        }

        // 2. Validasi Payload
        $request->validate([
            'license_code' => 'required|string',
            'package' => 'required|string',
            'business_name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:100',
            'business_type' => 'nullable|string|max:255',
            'business_address' => 'nullable|string',
            'business_description' => 'nullable|string|max:1000',
            'owner_name' => 'required|string|max:255',
            'owner_phone' => 'required|string|max:25',
            'owner_email' => 'nullable|email|max:255',
            'owner_password' => 'nullable|string|min:6',
            'piticoin_user_id' => 'nullable|string',
            'registered_by_id' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'duration_months' => 'nullable|integer|min:1',
        ]);

        $businessName = trim($request->input('business_name'));
        $rawDomain = trim($request->input('domain') ?: Str::slug($businessName));
        $domainSlug = Str::slug($rawDomain);

        if (empty($domainSlug)) {
            $domainSlug = 'store-' . Str::lower(Str::random(6));
        }

        // Cek keunikan domain / slug, jika sudah ada, tambahkan penomoran unik
        $baseSlug = $domainSlug;
        $counter = 1;
        while (Tenant::where('domain', $domainSlug)->exists()) {
            $counter++;
            $domainSlug = "{$baseSlug}-{$counter}";
        }

        // Tentukan business_type (grosir vs caffe)
        $rawType = strtolower($request->input('business_type') ?: '');
        $bType = 'caffe';
        if (str_contains($rawType, 'grosir') || str_contains($rawType, 'kelontong') || str_contains($rawType, 'sembako') || str_contains($rawType, 'wholesale')) {
            $bType = 'grosir';
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $request->input('owner_phone'));
        $ownerEmail = $request->input('owner_email') ?: ($cleanPhone ? "{$cleanPhone}@piticoin.id" : "{$domainSlug}@unimart.group");
        $durationMonths = (int) ($request->input('duration_months') ?: 1);

        // Paket Unimart
        $package = $request->input('package');
        $normalizedPackage = 'Bisnis';
        if (stripos($package, 'basic') !== false) {
            $normalizedPackage = 'Basic';
        } elseif (stripos($package, 'vip') !== false || stripos($package, 'enterprise') !== false || stripos($package, 'premium') !== false) {
            $normalizedPackage = 'Premium VIP';
        }

        // 3. Buat Record Tenant
        $tenant = Tenant::create([
            'name' => $businessName,
            'domain' => $domainSlug,
            'business_type' => $bType,
            'owner_name' => $request->input('owner_name'),
            'owner_email' => $ownerEmail,
            'owner_phone' => $request->input('owner_phone'),
            'status' => 'active',
            'trial_ends_at' => now()->addDays(7),
            'subscription_ends_at' => now()->addMonths($durationMonths),
            'city' => $request->input('city') ?: 'Indonesia',
            'address' => $request->input('business_address') ?: 'Indonesia',
            'is_marketplace_enabled' => true,
            'settings' => [
                'package' => $normalizedPackage,
                'license_code' => $request->input('license_code'),
                'piticoin_user_id' => $request->input('piticoin_user_id'),
                'registered_by_id' => $request->input('registered_by_id'),
                'payment_method' => $request->input('payment_method', 'GOLD'),
                'business_type' => $request->input('business_type'),
                'business_address' => $request->input('business_address'),
                'business_description' => $request->input('business_description'),
                'tables_count' => 15,
                'tax_rate' => 10,
                'service_charge' => 5,
                'external_source' => 'PITICOIN_HUB',
                'verified' => true,
            ],
        ]);

        // 4. Buat / Hubungkan User Owner
        $rawPassword = $request->input('owner_password') ?: 'Unimart@' . ($cleanPhone ? substr($cleanPhone, -4) : '1234');
        $user = User::where('email', $ownerEmail)->first();

        if (!$user) {
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $request->input('owner_name'),
                'email' => $ownerEmail,
                'phone' => $request->input('owner_phone'),
                'role' => 'owner',
                'password' => Hash::make($rawPassword),
                'pin' => strval(rand(1000, 9999)),
            ]);
        } else {
            // Update tenant_id jika user belum memiliki tenant
            if (!$user->tenant_id) {
                $user->tenant_id = $tenant->id;
                $user->role = 'owner';
                $user->save();
            }
        }

        Log::info("Toko baru berhasil didaftarkan via PitiCoin Hub: {$tenant->name} ({$tenant->domain})", [
            'tenant_id' => $tenant->id,
            'license_code' => $request->input('license_code'),
            'store_url' => $tenant->store_url,
        ]);

        // 5. Kembalikan Respons JSON
        return response()->json([
            'success' => true,
            'message' => 'Toko berhasil didaftarkan dan diaktifkan di Unimart.group',
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'domain' => $tenant->domain,
                'package' => $normalizedPackage,
                'store_url' => $tenant->store_url,
                'menu_url' => $tenant->store_url . '/menu',
                'subdomain_url' => $tenant->subdomain_url,
                'status' => $tenant->status,
                'subscription_ends_at' => $tenant->subscription_ends_at ? $tenant->subscription_ends_at->toIso8601String() : null,
            ],
            'owner' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'login_url' => url('/owner/login'),
                'initial_password' => $rawPassword,
            ],
            'redirect_url' => $tenant->store_url,
        ], 201);
    }
}
