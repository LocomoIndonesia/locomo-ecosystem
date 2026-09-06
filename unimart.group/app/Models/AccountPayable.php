<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountPayable extends Model
{
    use HasFactory;

    protected $table = 'accounts_payable';

    protected $fillable = [
        'tenant_id',
        'supplier_id',
        'purchase_id',
        'payable_number',
        'total_debt_amount',
        'remaining_amount',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_debt_amount' => 'integer',
        'remaining_amount' => 'integer',
        'due_date' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function payments()
    {
        return $this->hasMany(PayablePayment::class, 'payable_id');
    }
}
