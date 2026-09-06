<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AccountPayable;
use App\Models\PayablePayment;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountPayableController extends Controller
{
    public function index(Request $request)
    {
        $tenant = app('tenant');
        $status = $request->query('status', 'unpaid'); // unpaid, partially_paid, paid, all

        $query = AccountPayable::where('tenant_id', $tenant->id)
            ->with(['supplier', 'purchase', 'payments.processor']);

        if ($status !== 'all') {
            if ($status === 'unpaid') {
                $query->whereIn('status', ['unpaid', 'partially_paid']);
            } else {
                $query->where('status', $status);
            }
        }

        $payables = $query->orderBy('due_date')->paginate(15);

        $totalRemainingDebt = AccountPayable::where('tenant_id', $tenant->id)
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->sum('remaining_amount');

        $overdueCount = AccountPayable::where('tenant_id', $tenant->id)
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->where('due_date', '<', now()->toDateString())
            ->count();

        return view('owner.payables.index', compact('tenant', 'payables', 'totalRemainingDebt', 'overdueCount', 'status'));
    }

    public function recordPayment(Request $request, $id)
    {
        $tenant = app('tenant');
        $payable = AccountPayable::where('tenant_id', $tenant->id)->with('supplier', 'purchase')->findOrFail($id);

        $request->validate([
            'amount_paid' => 'required|integer|min:1000|max:' . $payable->remaining_amount,
            'payment_method' => 'required|string|in:cash,bank_transfer',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request, $tenant, $payable) {
                $amount = (int) $request->amount_paid;

                $activeShift = null;
                if ($request->payment_method === 'cash') {
                    $activeShift = Shift::where('tenant_id', $tenant->id)
                        ->where('user_id', Auth::id())
                        ->whereNull('closed_at')
                        ->first();
                }

                // 1. Buat record cicilan pembayaran
                PayablePayment::create([
                    'tenant_id' => $tenant->id,
                    'payable_id' => $payable->id,
                    'shift_id' => $activeShift ? $activeShift->id : null,
                    'payment_method' => $request->payment_method,
                    'amount_paid' => $amount,
                    'processed_by_user_id' => Auth::id(),
                    'notes' => $request->notes,
                ]);

                // 2. Potong sisa utang
                $payable->remaining_amount -= $amount;
                if ($payable->remaining_amount <= 0) {
                    $payable->remaining_amount = 0;
                    $payable->status = 'paid';
                    $payable->purchase->update(['payment_status' => 'paid']);
                } else {
                    $payable->status = 'partially_paid';
                    $payable->purchase->update(['payment_status' => 'partial']);
                }
                $payable->save();

                // 3. Potong total utang supplier
                $payable->supplier->decrement('current_debt', $amount);

                // 4. Jika tunai dan ada shift aktif: kurangi saldo kas fisik shift
                if ($activeShift) {
                    $activeShift->increment('total_supplier_cash_out', $amount);
                    $activeShift->decrement('system_ending_cash', $amount);
                }
            });

            return redirect()->route('owner.payables.index')->with('success', 'Pembayaran cicilan ke supplier berhasil dicatat.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mencatat pembayaran: ' . $e->getMessage());
        }
    }
}
