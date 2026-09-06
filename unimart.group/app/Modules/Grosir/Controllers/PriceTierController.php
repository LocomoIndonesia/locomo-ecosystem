<?php

namespace App\Modules\Grosir\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Modules\Grosir\Models\PriceTier;
use Illuminate\Http\Request;

class PriceTierController extends Controller
{
    public function index()
    {
        $tenant = app('tenant');
        $products = Product::where('tenant_id', $tenant->id)->orderBy('name')->get();
        $tiers = PriceTier::where('tenant_id', $tenant->id)->with('product')->orderBy('product_id')->orderBy('min_quantity')->get();

        return view('grosir::price-tiers', compact('tenant', 'products', 'tiers'));
    }

    public function store(Request $request)
    {
        $tenant = app('tenant');

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'min_quantity' => 'required|integer|min:2',
            'price_per_unit' => 'required|integer|min:0',
            'unit_name' => 'required|string|max:50',
        ]);

        $validated['tenant_id'] = $tenant->id;

        PriceTier::create($validated);

        return redirect()->route('owner.grosir.tiers.index')->with('success', 'Tier harga grosir berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        $tenant = app('tenant');
        $tier = PriceTier::where('tenant_id', $tenant->id)->findOrFail($id);
        $tier->delete();

        return redirect()->route('owner.grosir.tiers.index')->with('success', 'Tier harga berhasil dihapus.');
    }
}
