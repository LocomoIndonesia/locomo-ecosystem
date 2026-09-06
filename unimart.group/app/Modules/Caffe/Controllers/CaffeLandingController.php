<?php

namespace App\Modules\Caffe\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Http\Request;

class CaffeLandingController extends Controller
{
    /**
     * Tampilkan landing page eksklusif kafe / resto.
     */
    public function showLanding($alias)
    {
        $tenant = app('tenant');
        $products = Product::where('tenant_id', $tenant->id)
            ->where('is_available', true)
            ->get();

        $categories = $products->pluck('category')->unique()->filter();

        return view('caffe::landing', compact('tenant', 'alias', 'products', 'categories'));
    }

    /**
     * Tampilkan antarmuka QR Menu Meja untuk self-order pelanggan.
     */
    public function showQRMenu($alias, Request $request)
    {
        $tenant = app('tenant');
        $tableToken = $request->query('table');
        
        $table = null;
        if ($tableToken) {
            $table = Table::where('tenant_id', $tenant->id)->where('qr_token', $tableToken)->first();
        }

        $products = Product::where('tenant_id', $tenant->id)
            ->where('is_available', true)
            ->get();

        $tables = Table::where('tenant_id', $tenant->id)->get();

        return view('caffe::qr-menu', compact('tenant', 'alias', 'products', 'table', 'tables'));
    }
}
