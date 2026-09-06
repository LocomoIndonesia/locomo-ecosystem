<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AccountPayable;
use App\Models\AccountReceivable;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PayablePayment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\ReceivablePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialReportController extends Controller
{
    public function finance(Request $request)
    {
        $tenant = app('tenant');

        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());

        // 1. Total Penjualan / Omset
        $orders = Order::where('tenant_id', $tenant->id)
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->whereIn('payment_status', ['paid', 'debt', 'partial'])
            ->get();

        $totalRevenue = $orders->sum('total_amount');
        $cashRevenue = $orders->where('payment_status', 'paid')->sum('total_amount');
        $debtRevenue = $orders->where('payment_status', 'debt')->sum('total_amount');

        // 2. Harga Pokok Penjualan (HPP / COGS)
        $orderIds = $orders->pluck('id');
        $totalCogs = OrderItem::where('tenant_id', $tenant->id)
            ->whereIn('order_id', $orderIds)
            ->selectRaw('SUM(quantity * cost_price) as total_cogs')
            ->value('total_cogs') ?? 0;

        // 3. Laba Kotor
        $grossProfit = $totalRevenue - $totalCogs;

        // 4. Beban Operasional (Petty Cash / Expenses)
        $expenses = Expense::where('tenant_id', $tenant->id)
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->get();

        $totalExpenses = $expenses->sum('amount');

        // 5. Laba Bersih
        $netProfit = $grossProfit - $totalExpenses;

        // 6. Arus Kas Riil (Cash Flow)
        // Kas Masuk: Penjualan tunai langsung + pelunasan piutang pelanggan
        $receivablePaymentsIn = ReceivablePayment::where('tenant_id', $tenant->id)
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->where('payment_method', 'cash')
            ->sum('amount_paid');

        $totalCashIn = $cashRevenue + $receivablePaymentsIn;

        // Kas Keluar: Pembelian tunai ke supplier + cicilan utang supplier + beban kas
        $cashPurchases = Purchase::where('tenant_id', $tenant->id)
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->where('payment_method', 'cash')
            ->sum('total_amount');

        $payablePaymentsOut = PayablePayment::where('tenant_id', $tenant->id)
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->where('payment_method', 'cash')
            ->sum('amount_paid');

        $totalCashOut = $cashPurchases + $payablePaymentsOut + $totalExpenses;
        $netCashFlow = $totalCashIn - $totalCashOut;

        // 7. Saldo Neraca Berjalan (Current Balance Snapshot)
        $totalActiveReceivables = AccountReceivable::where('tenant_id', $tenant->id)
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->sum('remaining_amount');

        $totalActivePayables = AccountPayable::where('tenant_id', $tenant->id)
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->sum('remaining_amount');

        $inventoryValuation = Product::where('tenant_id', $tenant->id)
            ->selectRaw('SUM(stock * cost_price) as total_val')
            ->value('total_val') ?? 0;

        return view('owner.reports.finance', compact(
            'tenant',
            'startDate',
            'endDate',
            'totalRevenue',
            'cashRevenue',
            'debtRevenue',
            'totalCogs',
            'grossProfit',
            'totalExpenses',
            'netProfit',
            'totalCashIn',
            'totalCashOut',
            'netCashFlow',
            'totalActiveReceivables',
            'totalActivePayables',
            'inventoryValuation',
            'expenses'
        ));
    }
}
