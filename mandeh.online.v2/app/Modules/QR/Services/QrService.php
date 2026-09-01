<?php

namespace App\Modules\QR\Services;

use App\Modules\QR\Models\QrCode;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrGenerator;
use Illuminate\Support\Str;

class QrService
{
    /**
     * Generate a new QR code for a business.
     */
    public function generateForBusiness(string $businessId): QrCode
    {
        return QrCode::create([
            'business_id' => $businessId,
            'code' => Str::random(12),
            'version' => '1.0',
            'is_active' => true,
        ]);
    }

    /**
     * Get the QR image SVG content.
     */
    public function getQrSvg(string $code): string
    {
        $url = route('qr.landing', ['code' => $code]);
        
        return QrGenerator::size(300)
            ->format('svg')
            ->generate($url);
    }
}
