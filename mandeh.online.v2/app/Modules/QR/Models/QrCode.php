<?php

namespace App\Modules\QR\Models;

use App\Models\BaseModel;
use App\Modules\Business\Models\Business;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrCode extends BaseModel
{
    protected $fillable = [
        'business_id',
        'code',
        'version',
        'is_active',
        'last_scanned_at',
        'scan_count',
    ];

    /**
     * Get the business that owns the QR code.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
