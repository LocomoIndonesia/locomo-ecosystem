<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    public function index()
    {
        $tenant = app('tenant');
        $suppliers = Supplier::where('tenant_id', $tenant->id)
            ->withCount('purchases')
            ->orderBy('name')
            ->get();

        $totalPayableDebt = $suppliers->sum('current_debt');

        return view('owner.suppliers.index', compact('tenant', 'suppliers', 'totalPayableDebt'));
    }

    public function store(Request $request)
    {
        $tenant = app('tenant');

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string',
            'bank_account_info' => 'nullable|string',
        ]);

        $validated['tenant_id'] = $tenant->id;
        $validated['current_debt'] = 0;

        Supplier::create($validated);

        return redirect()->route('owner.suppliers.index')->with('success', 'Pemasok berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $tenant = app('tenant');
        $supplier = Supplier::where('tenant_id', $tenant->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string',
            'bank_account_info' => 'nullable|string',
        ]);

        $supplier->update($validated);

        return redirect()->route('owner.suppliers.index')->with('success', 'Data pemasok berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $tenant = app('tenant');
        $supplier = Supplier::where('tenant_id', $tenant->id)->findOrFail($id);

        if ($supplier->current_debt > 0) {
            return redirect()->route('owner.suppliers.index')->with('error', 'Pemasok tidak dapat dihapus karena masih memiliki saldo utang aktif.');
        }

        $supplier->delete();

        return redirect()->route('owner.suppliers.index')->with('success', 'Pemasok berhasil dihapus.');
    }
}
