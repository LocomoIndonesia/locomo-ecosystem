<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AccountPayable;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Shift;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index()
    {
        $tenant = app('tenant');
        $purchases = Purchase::where('tenant_id', $tenant->id)
            ->with(['supplier', 'items.product', 'accountPayable'])
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->paginate(15);

        return view('owner.purchases.index', compact('tenant', 'purchases'));
    }

    public function create()
    {
        $tenant = app('tenant');
        $suppliers = Supplier::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('name')->get();
        $products = Product::where('tenant_id', $tenant->id)->orderBy('name')->get();

        return view('owner.purchases.create', compact('tenant', 'suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $tenant = app('tenant');

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'supplier_invoice_no' => 'nullable|string|max:100',
            'purchase_date' => 'required|date',
            'payment_method' => 'required|string|in:cash,bank_transfer,debt',
            'due_date' => 'nullable|required_if:payment_method,debt|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.buy_price' => 'required|integer|min:0',
        ]);

        $supplier = Supplier::where('tenant_id', $tenant->id)->findOrFail($request->supplier_id);

        try {
            DB::transaction(function () use ($request, $tenant, $supplier) {
                $totalAmount = 0;
                $itemsData = [];

                // 1. Hitung total
                foreach ($request->items as $item) {
                    $subtotal = $item['quantity'] * $item['buy_price'];
                    $totalAmount += $subtotal;
                    $itemsData[] = [
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'buy_price' => $item['buy_price'],
                        'subtotal' => $subtotal,
                        'notes' => $item['notes'] ?? null,
                    ];
                }

                $purchaseNumber = 'PO-' . date('Ymd') . '-' . str_pad(Purchase::where('tenant_id', $tenant->id)->count() + 1, 4, '0', STR_PAD_LEFT);
                $isDebt = $request->payment_method === 'debt';

                // 2. Buat Purchase
                $purchase = Purchase::create([
                    'tenant_id' => $tenant->id,
                    'supplier_id' => $supplier->id,
                    'purchase_number' => $purchaseNumber,
                    'supplier_invoice_no' => $request->supplier_invoice_no,
                    'purchase_date' => $request->purchase_date,
                    'total_amount' => $totalAmount,
                    'payment_status' => $isDebt ? 'debt' : 'paid',
                    'payment_method' => $request->payment_method,
                    'due_date' => $isDebt ? $request->due_date : null,
                    'notes' => $request->notes,
                    'created_by_user_id' => Auth::id(),
                ]);

                // 3. Simpan item dan update stok serta Moving Average HPP
                foreach ($itemsData as $row) {
                    PurchaseItem::create([
                        'tenant_id' => $tenant->id,
                        'purchase_id' => $purchase->id,
                        'product_id' => $row['product_id'],
                        'quantity' => $row['quantity'],
                        'buy_price' => $row['buy_price'],
                        'subtotal' => $row['subtotal'],
                        'notes' => $row['notes'],
                    ]);

                    $product = Product::where('tenant_id', $tenant->id)
                        ->where('id', $row['product_id'])
                        ->lockForUpdate()
                        ->first();

                    if ($product) {
                        $product->updateMovingAverageCost((int) $row['quantity'], (int) $row['buy_price']);
                    }
                }

                // 4. Jika Utang Tempo: Catat ke Accounts Payable
                if ($isDebt) {
                    $payableNumber = 'AP-' . date('Ymd') . '-' . str_pad(AccountPayable::where('tenant_id', $tenant->id)->count() + 1, 4, '0', STR_PAD_LEFT);

                    AccountPayable::create([
                        'tenant_id' => $tenant->id,
                        'supplier_id' => $supplier->id,
                        'purchase_id' => $purchase->id,
                        'payable_number' => $payableNumber,
                        'total_debt_amount' => $totalAmount,
                        'remaining_amount' => $totalAmount,
                        'due_date' => $request->due_date,
                        'status' => 'unpaid',
                        'notes' => 'Faktur Pembelian ' . $purchaseNumber,
                    ]);

                    $supplier->increment('current_debt', $totalAmount);
                } 
                // 5. Jika Tunai: Kurangi kas shift aktif jika kasir sedang membuka shift
                elseif ($request->payment_method === 'cash') {
                    $activeShift = Shift::where('tenant_id', $tenant->id)
                        ->where('user_id', Auth::id())
                        ->whereNull('closed_at')
                        ->first();

                    if ($activeShift) {
                        $activeShift->increment('total_supplier_cash_out', $totalAmount);
                        $activeShift->decrement('system_ending_cash', $totalAmount);
                    }
                }
            });

            return redirect()->route('owner.purchases.index')->with('success', 'Faktur pengadaan stok berhasil dicatat.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memproses pengadaan: ' . $e->getMessage());
        }
    }
}
