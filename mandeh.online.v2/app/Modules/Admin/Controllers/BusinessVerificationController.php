<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use Illuminate\Http\Request;

class BusinessVerificationController extends Controller
{
    public function index()
    {
        $businesses = Entity::where('subscription_package', '!=', 'free')
            ->where('is_active', false)
            ->whereNotNull('payment_proof_path')
            ->with('owner')
            ->latest()
            ->paginate(20);

        return view('admin::verification.index', compact('businesses'));
    }

    public function verify(Request $request, $id)
    {
        $request->validate([
            'decision' => 'required|in:approve,reject',
        ]);

        $business = Entity::findOrFail($id);

        if ($request->decision === 'approve') {
            $business->update([
                'is_active' => true,
                'payment_verified_at' => now(),
            ]);
            $message = 'Pembayaran bisnis berhasil diverifikasi dan diaktifkan!';
        } else {
            // Hapus bukti bayar agar user bisa upload ulang bukti baru
            $business->update([
                'payment_proof_path' => null,
            ]);
            $message = 'Pembayaran ditolak. Bukti pembayaran telah dihapus agar pemilik dapat mengunggah bukti baru.';
        }

        return back()->with('success', $message);
    }
}
