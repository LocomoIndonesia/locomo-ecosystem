<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AccountReceivable;
use App\Models\ReceivablePayment;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountReceivableController extends Controller
{
    public function index(Request $request)
    {
        $tenant = app('tenant');
        $status = $request->query('status', 'unpaid'); // unpaid, partially_paid, paid, all

        $query = AccountReceivable::where('tenant_id', $tenant->id)
            ->with(['customer', 'order', 'payments.processor']);

        if ($status !== 'all') {
            if ($status === 'unpaid') {
                $query->whereIn('status', ['unpaid', 'partially_paid']);
            } else {
                $query->where('status', $status);
            }
        }

        $receivables = $query->orderBy('due_date')->paginate(15);

        $totalRemainingDebt = AccountReceivable::where('tenant_id', $tenant->id)
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->sum('remaining_amount');

        $overdueCount = AccountReceivable::where('tenant_id', $tenant->id)
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->where('due_date', '<', now()->toDateString())
            ->count();

        return view('owner.receivables.index', compact('tenant', 'receivables', 'totalRemainingDebt', 'overdueCount', 'status'));
    }

    public function recordPayment(Request $request, $id)
    {
        $tenant = app('tenant');
        $receivable = AccountReceivable::where('tenant_id', $tenant->id)->with('customer', 'order')->findOrFail($id);

        $request->validate([
            'amount_paid' => 'required|integer|min:1000|max:' . $receivable->remaining_amount,
            'payment_method' => 'required|string|in:cash,qris_online,bank_transfer',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request, $tenant, $receivable) {
                $amount = (int) $request->amount_paid;

                $activeShift = null;
                if ($request->payment_method === 'cash') {
                    $activeShift = Shift::where('tenant_id', $tenant->id)
                        ->where('user_id', Auth::id())
                        ->whereNull('closed_at')
                        ->first();
                }

                // 1. Catat setoran cicilan piutang
                ReceivablePayment::create([
                    'tenant_id' => $tenant->id,
                    'receivable_id' => $receivable->id,
                    'shift_id' => $activeShift ? $activeShift->id : null,
                    'payment_method' => $request->payment_method,
                    'amount_paid' => $amount,
                    'processed_by_user_id' => Auth::id(),
                    'notes' => $request->notes,
                ]);

                // 2. Potong sisa piutang
                $receivable->remaining_amount -= $amount;
                if ($receivable->remaining_amount <= 0) {
                    $receivable->remaining_amount = 0;
                    $receivable->status = 'paid';
                    $receivable->order->update(['payment_status' => 'paid']);
                } else {
                    $receivable->status = 'partially_paid';
                    $receivable->order->update(['payment_status' => 'partial']);
                }
                $receivable->save();

                // 3. Potong saldo utang pelanggan
                $receivable->customer->decrement('current_debt', $amount);

                // 4. Jika tunai: tambahkan uang masuk ke kas shift kasir
                if ($activeShift) {
                    $activeShift->increment('total_cash_sales', $amount);
                    $activeShift->increment('system_ending_cash', $amount);
                }
            });

            return redirect()->route('owner.receivables.index')->with('success', 'Penerimaan cicilan piutang berhasil dicatat.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mencatat pembayaran piutang: ' . $e->getMessage());
        }
    }
}
