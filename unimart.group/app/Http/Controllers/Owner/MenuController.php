<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    /**
     * Tampilkan daftar menu/produk milik tenant.
     */
    public function index()
    {
        $tenant = app('tenant');
        $package = $tenant->settings['package'] ?? 'Bisnis';
        
        $products = Product::where('tenant_id', $tenant->id)->get();

        return view('owner.menus', compact('tenant', 'package', 'products'));
    }

    /**
     * Simpan menu baru ke database.
     */
    public function store(Request $request)
    {
        $tenant = app('tenant');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'category' => ['required', 'string', Rule::in(['makanan', 'minuman', 'dessert'])],
            'image_url' => ['nullable', 'string', 'max:2048'],
        ]);

        // Gunakan default image jika kosong
        $imageUrl = $request->image_url;
        if (empty($imageUrl)) {
            if ($request->category === 'minuman') {
                $imageUrl = 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&q=80&w=400';
            } elseif ($request->category === 'dessert') {
                $imageUrl = 'https://images.unsplash.com/photo-1564355808539-22fda35bed7e?auto=format&fit=crop&q=80&w=400';
            } else {
                $imageUrl = 'https://images.unsplash.com/photo-1603133872878-6966b4588045?auto=format&fit=crop&q=80&w=400';
            }
        }

        Product::create([
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'price' => $request->price,
            'stock' => $request->stock,
            'category' => $request->category,
            'image_url' => $imageUrl,
        ]);

        return redirect()->route('owner.menus')->with('success', 'Menu berhasil ditambahkan!');
    }

    /**
     * Perbarui data menu yang sudah ada.
     */
    public function update(Request $request, $id)
    {
        $tenant = app('tenant');

        $product = Product::where('tenant_id', $tenant->id)
            ->where('id', $id)
            ->first();

        if (!$product) {
            return back()->withErrors(['message' => 'Menu tidak ditemukan.']);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'category' => ['required', 'string', Rule::in(['makanan', 'minuman', 'dessert'])],
            'image_url' => ['nullable', 'string', 'max:2048'],
        ]);

        $product->update([
            'name' => $request->name,
            'price' => $request->price,
            'stock' => $request->stock,
            'category' => $request->category,
            'image_url' => $request->image_url ?? $product->image_url,
        ]);

        return redirect()->route('owner.menus')->with('success', 'Menu berhasil diperbarui!');
    }

    /**
     * Hapus menu dari database.
     */
    public function destroy($id)
    {
        $tenant = app('tenant');

        $product = Product::where('tenant_id', $tenant->id)
            ->where('id', $id)
            ->first();

        if ($product) {
            $product->delete();
            return redirect()->route('owner.menus')->with('success', 'Menu berhasil dihapus!');
        }

        return back()->withErrors(['message' => 'Menu gagal dihapus atau tidak ditemukan.']);
    }
}
