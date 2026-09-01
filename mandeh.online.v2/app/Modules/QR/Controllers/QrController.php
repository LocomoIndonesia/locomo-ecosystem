<?php

namespace App\Modules\QR\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\QR\Models\QrCode;
use Illuminate\View\View;

class QrController extends Controller
{
    /**
     * Display the public landing page for a QR code.
     */
    public function show(string $code): View
    {
        $qrCode = QrCode::where('code', $code)->with('business')->firstOrFail();
        
        // Track scan
        $qrCode->increment('scan_count');
        $qrCode->update(['last_scanned_at' => now()]);

        return view('qr::landing', [
            'qrCode' => $qrCode,
            'business' => $qrCode->business,
        ]);
    }
}
