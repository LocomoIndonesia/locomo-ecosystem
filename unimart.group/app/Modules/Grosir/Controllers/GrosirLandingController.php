<?php

namespace App\Modules\Grosir\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Modules\Grosir\Models\PriceTier;
use Illuminate\Http\Request;

class GrosirLandingController extends Controller
{
    /**
     * Tampilkan landing page khusus distributor grosir.
     */
    public function showLanding($alias)
    {
        $tenant = app('tenant');
        $products = Product::where('tenant_id', $tenant->id)
            ->where('is_available', true)
            ->get();

        $productIds = $products->pluck('id');
        $tiers = PriceTier::where('tenant_id', $tenant->id)
            ->whereIn('product_id', $productIds)
            ->orderBy('min_quantity')
            ->get()
            ->groupBy('product_id');

        return view('grosir::landing', compact('tenant', 'alias', 'products', 'tiers'));
    }
}
