<?php

namespace App\Modules\Marketplace\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Http\Request;

class MarketplaceHomeController extends Controller
{
    /**
     * Tampilkan halaman utama Marketplace Publik Unimart.
     */
    public function index(Request $request)
    {
        $search = $request->query('q');
        $city = $request->query('city');
        $businessType = $request->query('type');

        // Query produk dari SELURUH toko yang tayang di marketplace
        $productsQuery = Product::withoutGlobalScopes()
            ->where('is_published', true)
            ->where('is_available', true)
            ->where('is_active', true)
            ->whereHas('tenant', function ($q) use ($city, $businessType) {
                $q->whereIn('status', ['trial', 'active'])
                  ->where('is_marketplace_enabled', true);

                if ($city) {
                    $q->where('city', $city);
                }
                if ($businessType) {
                    $q->where('business_type', $businessType);
                }
            })
            ->with('tenant');

        if ($search) {
            $productsQuery->where('name', 'ilike', '%' . $search . '%');
        }

        $products = $productsQuery->latest()->paginate(16);

        // Daftar Toko Mitra
        $stores = Tenant::whereIn('status', ['trial', 'active'])
            ->where('is_marketplace_enabled', true)
            ->withCount('products')
            ->take(8)
            ->get();

        $cities = Tenant::whereNotNull('city')->pluck('city')->unique()->filter();

        return view('marketplace::home', compact('products', 'stores', 'search', 'city', 'businessType', 'cities'));
    }
}
