<?php

namespace App\Modules\Grosir\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AccountReceivable;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shift;
use App\Modules\Grosir\Models\PriceTier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GrosirPOSController extends Controller
{
    public function showPOS($alias)
    {
        $tenant = app('tenant');
        $products = Product::where('tenant_id', $tenant->id)->where('is_available', true)->get();
        $customers = Customer::where('tenant_id', $tenant->id)->get();
        
        $productIds = $products->pluck('id');
        $tiers = PriceTier::where('tenant_id', $tenant->id)
            ->whereIn('product_id', $productIds)
            ->orderBy('min_quantity')
            ->get()
            ->groupBy('product_id');

        $shift = Shift::where('tenant_id', $tenant->id)
            ->where('user_id', Auth::id())
            ->whereNull('closed_at')
            ->first();

        if (!$shift) {
            $shift = Shift::create([
                'tenant_id' => $tenant->id,
                'user_id' => Auth::id(),
                'opened_at' => now(),
                'starting_cash' => 500000,
                'system_ending_cash' => 500000,
            ]);
        }

        return view('grosir::pos', compact('tenant', 'alias', 'products', 'customers', 'tiers', 'shift'));
    }

    public function checkout(Request $request, $alias)
    {
        $tenant = app('tenant');

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|string|in:cash,bank_transfer,debt',
            'customer_id' => 'nullable|required_if:payment_method,debt|exists:customers,id',
            'due_date' => 'nullable|required_if:payment_method,debt|date',
        ]);

        $shift = Shift::where('tenant_id', $tenant->id)
            ->where('user_id', Auth::id())
            ->whereNull('closed_at')
            ->first();

        try {
            $order = DB::transaction(function () use ($request, $tenant, $shift) {
                $totalAmount = 0;
                $processedItems = [];

                foreach ($request->items as $item) {
                    $product = Product::where('tenant_id', $tenant->id)
                        ->where('id', $item['product_id'])
                        ->lockForUpdate()
                        ->first();

                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Stok tidak mencukupi untuk item grosir: " . $product->name);
                    }

                    // Cek apakah ada tier harga grosir yang cocok
                    $matchedTier = PriceTier::where('tenant_id', $tenant->id)
                        ->where('product_id', $product->id)
                        ->where('min_quantity', '<=', $item['quantity'])
                        ->orderByDesc('min_quantity')
                        ->first();

                    $pricePerUnit = $matchedTier ? $matchedTier->price_per_unit : $product->price;
                    $subtotal = $pricePerUnit * $item['quantity'];
                    $totalAmount += $subtotal;

                    // Potong stok
                    $product->decrement('stock', $item['quantity']);

                    $processedItems[] = [
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'price' => $pricePerUnit,
                        'cost_price' => $product->cost_price,
                        'notes' => $matchedTier ? 'Harga Grosir Tier ' . $matchedTier->min_quantity . ' ' . $matchedTier->unit_name : 'Harga Eceran Normal',
                    ];
                }

                $isDebt = $request->payment_method === 'debt';
                $orderNumber = 'GROSIR-' . date('YmdHis') . '-' . rand(10, 99);

                $order = Order::create([
                    'tenant_id' => $tenant->id,
                    'shift_id' => $shift ? $shift->id : null,
                    'customer_id' => $request->customer_id ?? null,
                    'order_number' => $orderNumber,
                    'order_type' => 'takeaway',
                    'order_source' => 'pos',
                    'kitchen_status' => 'delivered', // Grosir langsung serah terima barang
                    'payment_status' => $isDebt ? 'debt' : 'paid',
                    'total_amount' => $totalAmount,
                    'notes' => 'Faktur Penjualan Grosir',
                ]);

                foreach ($processedItems as $row) {
                    OrderItem::create([
                        'tenant_id' => $tenant->id,
                        'order_id' => $order->id,
                        'product_id' => $row['product_id'],
                        'quantity' => $row['quantity'],
                        'price' => $row['price'],
                        'cost_price' => $row['cost_price'],
                        'notes' => $row['notes'],
                        'status' => 'delivered',
                    ]);
                }

                if ($isDebt) {
                    $receivableNumber = 'AR-G-' . date('Ymd') . '-' . str_pad(AccountReceivable::where('tenant_id', $tenant->id)->count() + 1, 4, '0', STR_PAD_LEFT);

                    AccountReceivable::create([
                        'tenant_id' => $tenant->id,
                        'customer_id' => $request->customer_id,
                        'order_id' => $order->id,
                        'receivable_number' => $receivableNumber,
                        'total_debt_amount' => $totalAmount,
                        'remaining_amount' => $totalAmount,
                        'due_date' => $request->due_date ?? now()->addDays(30)->toDateString(),
                        'status' => 'unpaid',
                        'notes' => 'Faktur Grosir ' . $orderNumber,
                    ]);

                    $cust = Customer::find($request->customer_id);
                    if ($cust) {
                        $cust->increment('current_debt', $totalAmount);
                    }

                    if ($shift) {
                        $shift->increment('total_debt_sales', $totalAmount);
                    }
                } else {
                    $order->payments()->create([
                        'tenant_id' => $tenant->id,
                        'payment_method' => $request->payment_method,
                        'amount_paid' => $totalAmount,
                        'status' => 'success',
                        'processed_by_user_id' => Auth::id(),
                    ]);

                    if ($request->payment_method === 'cash' && $shift) {
                        $shift->increment('total_cash_sales', $totalAmount);
                        $shift->increment('system_ending_cash', $totalAmount);
                    }
                }

                return $order;
            });

            return response()->json([
                'success' => true,
                'message' => 'Faktur grosir berhasil diproses!',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total_amount' => $order->total_amount
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
