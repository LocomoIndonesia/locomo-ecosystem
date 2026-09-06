<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Tenant\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'opened_at',
        'closed_at',
        'starting_cash',
        'actual_ending_cash',
        'system_ending_cash',
        'total_cash_sales',
        'total_debt_sales',
        'total_supplier_cash_out',
        'cash_difference',
        'notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'starting_cash' => 'integer',
        'actual_ending_cash' => 'integer',
        'system_ending_cash' => 'integer',
        'total_cash_sales' => 'integer',
        'total_debt_sales' => 'integer',
        'total_supplier_cash_out' => 'integer',
        'cash_difference' => 'integer',
    ];

    /**
     * Relasi ke User (Kasir yang membuka shift).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Petty Cash Expenses yang terjadi dalam shift ini.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Relasi ke Orders yang dicatat selama shift ini.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Relasi ke pembayaran cicilan utang supplier selama shift ini.
     */
    public function payablePayments(): HasMany
    {
        return $this->hasMany(PayablePayment::class);
    }

    /**
     * Relasi ke penerimaan setoran cicilan piutang pelanggan selama shift ini.
     */
    public function receivablePayments(): HasMany
    {
        return $this->hasMany(ReceivablePayment::class);
    }
}
